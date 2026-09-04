<?php

namespace App\Http\Controllers;

use App\Contracts\BookingIntentInterpreter;
use App\Models\Appointment;
use App\Models\Conversation;
use App\Services\BookingEngine;
use App\Services\ConversationManager;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WhatsAppSimulatorController extends Controller
{
    public function __construct(
        private ConversationManager $conversations,
        private BookingEngine $booking,
        private BookingIntentInterpreter $interpreter,
    ) {}

    public function index(Request $request): View
    {
        $business = app('activeBusiness');
        $conversation = $this->selectedConversation($request);
        $settings = $business->settings()->firstOrCreate([]);
        $whatsappSettings = $this->whatsappSettings($business);

        return view('whatsapp-simulator.index', [
            'business' => $business,
            'whatsappSettings' => $whatsappSettings,
            'configuredPhone' => $this->normalizePhone($whatsappSettings['phone'] ?? ''),
            'conversations' => $business->conversations()
                ->with(['whatsappContact', 'state'])
                ->latest('last_message_at')
                ->latest()
                ->take(12)
                ->get(),
            'conversation' => $conversation,
            'messages' => $conversation
                ? $conversation->messages()->oldest()->get()
                : collect(),
            'state' => $conversation?->state,
            'channelStatus' => ($settings->whatsapp_settings['status'] ?? 'not_configured'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = app('activeBusiness');
        $attributes = $request->validate([
            'phone' => ['required', 'string', 'max:60'],
            'name' => ['nullable', 'string', 'max:140'],
            'body' => ['required', 'string', 'max:1000'],
        ]);

        $contact = $this->conversations->contactForWhatsapp(
            $business,
            $this->normalizePhone($attributes['phone']),
            $attributes['name'] ?: null,
            metadata: ['simulated' => true],
        );
        $conversation = $this->conversations->openConversation($business, $contact);

        $this->conversations->recordIncoming($conversation, [
            'external_message_id' => 'sim-'.Str::uuid(),
            'body' => $attributes['body'],
            'payload' => ['source' => 'internal_simulator'],
            'received_at' => now(),
        ]);

        $this->conversations->recordOutgoing($conversation, $this->replyFor($business, $conversation->fresh(['state', 'whatsappContact']), $attributes['body']));

        return redirect()->route('whatsapp-simulator.index', ['conversation' => $conversation->id])
            ->with('status', 'Mensaje simulado procesado.');
    }

    public function reset(Conversation $conversation): RedirectResponse
    {
        abort_unless($conversation->business_id === app('activeBusiness')->id, 404);

        $conversation->state()->delete();
        $conversation->update([
            'intent' => null,
            'current_step' => 'idle',
            'appointment_id' => null,
        ]);

        $this->conversations->recordOutgoing($conversation, 'Conversacion reiniciada. Escribe "quiero agendar" para comenzar de nuevo.');

        return redirect()->route('whatsapp-simulator.index', ['conversation' => $conversation->id]);
    }

    private function selectedConversation(Request $request): ?Conversation
    {
        $business = app('activeBusiness');
        $conversationId = $request->integer('conversation');

        return $conversationId
            ? $business->conversations()->with(['whatsappContact', 'state'])->findOrFail($conversationId)
            : $business->conversations()->with(['whatsappContact', 'state'])->latest('last_message_at')->latest()->first();
    }

    private function replyFor($business, Conversation $conversation, string $body): string
    {
        $state = $conversation->state?->state ?? 'idle';
        $data = $conversation->state?->data ?? [];
        $parsed = $this->interpreter->interpret($business, $body, [
            ...$data,
            'intent' => $conversation->intent,
            'state' => $state,
        ]);

        if ($parsed['reset'] ?? false) {
            $this->conversations->updateState($conversation, 'idle');
            $conversation->update(['intent' => null, 'current_step' => 'idle']);

            return 'Listo. Reinicie el flujo. Escribe "quiero agendar" para iniciar una reserva.';
        }

        $whatsappSettings = $this->whatsappSettings($business);

        if ($state === 'idle' && ($parsed['intent'] ?? null) !== 'booking') {
            return $whatsappSettings['welcome_message'].' Puedes escribir "quiero agendar" para probar una reserva.';
        }

        $data = $this->mergeBookingData($data, $parsed);
        $service = $this->booking->service($business, $data['service_id'] ?? null);

        if (! $service) {
            $this->setConversationState($conversation, 'booking', 'awaiting_service', $data);
            if ($state === 'awaiting_service') {
                return "No encontre ese servicio. Prueba escribiendo uno de estos nombres:\n\n".$this->serviceList($business);
            }

            return "Claro. Que servicio deseas reservar?\n\n".$this->serviceList($business);
        }

        $data['service_id'] = $service->id;
        $professionals = $this->booking->professionalsForService($business, $service);

        if ($professionals->isEmpty()) {
            $this->setConversationState($conversation, 'booking', 'awaiting_professional', $data);

            return 'Ese servicio no tiene profesionales activos asociados. Configuralos en TREBBIA antes de reservar.';
        }

        if (empty($data['professional_id']) && $professionals->count() === 1) {
            $data['professional_id'] = $professionals->first()->id;
        }

        $professional = $data['professional_id']
            ? $professionals->firstWhere('id', (int) $data['professional_id'])
            : null;

        if (! $professional) {
            $this->setConversationState($conversation, 'booking', 'awaiting_professional', $data);

            if ($state === 'awaiting_professional') {
                return 'No encontre ese profesional para el servicio seleccionado. Escribe el nombre tal como aparece en la lista.';
            }

            return "Perfecto: {$service->name}. Con que profesional deseas atenderte?\n\n".$professionals->pluck('name')->implode("\n");
        }

        $data['professional_id'] = $professional->id;

        if (empty($data['date'])) {
            $this->setConversationState($conversation, 'booking', 'awaiting_date', $data);

            return 'Bien. Que fecha deseas? Puedes escribir manana, lunes, 2026-09-07, 2026/09/07 o 7 de septiembre.';
        }

        $date = CarbonImmutable::parse($data['date'], $business->timezone);
        $slots = $this->availableSlotStrings($business, $service, $professional->id, $date);

        if ($slots === []) {
            $this->setConversationState($conversation, 'booking', 'awaiting_date', $data);

            return $whatsappSettings['unavailable_message'];
        }

        $data['slots'] = $slots;

        if (empty($data['time']) && ! empty($data['time_after'])) {
            $data['time'] = collect($slots)->first(fn (string $slot): bool => $slot >= $data['time_after']);
        }

        if (empty($data['time'])) {
            $this->setConversationState($conversation, 'booking', 'awaiting_time', $data);

            return "Tengo estos horarios disponibles:\n\n".implode("\n", $slots)."\n\nEscribe el horario que deseas confirmar.";
        }

        if (! in_array($data['time'], $slots, true)) {
            $this->setConversationState($conversation, 'booking', 'awaiting_time', $data);

            return "Ese horario no esta disponible. Tengo estas opciones:\n\n".implode("\n", $slots)."\n\nEscribe el horario que prefieres.";
        }

        return $this->confirmAppointment($business, $conversation, $data, $data['time']);
    }

    private function confirmAppointment($business, Conversation $conversation, array $data, string $time): string
    {
        $contact = $conversation->whatsappContact;
        $client = $this->booking->findOrCreateClient($business, [
            'client_name' => $contact->name ?: 'Cliente WhatsApp '.$contact->phone,
            'client_phone' => $contact->phone,
        ]);
        $startsAt = CarbonImmutable::parse($data['date'].' '.$time, $business->timezone);

        try {
            $appointment = $this->booking->createAppointment($business, [
                'client_id' => $client->id,
                'service_id' => $data['service_id'],
                'professional_id' => $data['professional_id'],
                'starts_at' => $startsAt,
                'status' => $this->whatsappSettings($business)['appointment_status'] ?? 'scheduled',
                'source_channel' => Appointment::SOURCE_WHATSAPP,
                'source_reference' => 'conversation:'.$conversation->id,
                'source_metadata' => ['simulated' => true],
            ]);
        } catch (ValidationException $exception) {
            return 'Ese horario dejo de estar disponible. Vuelve a consultar otra fecha u horario.';
        }

        $conversation->update([
            'appointment_id' => $appointment->id,
            'status' => Conversation::STATUS_CLOSED,
            'current_step' => 'completed',
        ]);
        $this->conversations->updateState($conversation, 'completed', [
            'appointment_id' => $appointment->id,
        ]);

        return $this->whatsappSettings($business)['confirmation_message']."\n\nServicio: {$appointment->service->name}\nFecha: ".$appointment->starts_at->format('d/m/Y')."\nHora: ".$appointment->starts_at->format('H:i')."\nProfesional: {$appointment->professional->name}";
    }

    private function setConversationState(Conversation $conversation, ?string $intent, string $step, array $data = []): void
    {
        $conversation->update([
            'intent' => $intent,
            'current_step' => $step,
        ]);
        $this->conversations->updateState($conversation, $step, $data, now()->addHours(24));
    }

    private function serviceList($business): string
    {
        $services = $this->booking->services($business)->pluck('name');

        return $services->isEmpty()
            ? 'No hay servicios activos configurados.'
            : $services->implode("\n");
    }

    private function mergeBookingData(array $data, array $parsed): array
    {
        foreach (['service_id', 'professional_id', 'date', 'time', 'time_after'] as $key) {
            if (array_key_exists($key, $parsed)) {
                $data[$key] = $parsed[$key];
            }
        }

        return $data;
    }

    private function availableSlotStrings($business, $service, int $professionalId, CarbonImmutable $date): array
    {
        return $this->booking->availableSlots($business, $service, $professionalId, $date)
            ->take(8)
            ->map->format('H:i')
            ->values()
            ->all();
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: $value;
    }

    private function whatsappSettings($business): array
    {
        return array_merge([
            'enabled' => false,
            'phone' => $business->phone ?? '',
            'display_name' => $business->name,
            'entry_message' => 'Hola, quiero agendar una cita',
            'welcome_message' => 'Hola, soy el asistente de reservas de '.$business->name.'. Te ayudo a encontrar un horario disponible.',
            'unavailable_message' => 'No encontre horarios disponibles para esa opcion. Probemos con otra fecha u horario.',
            'confirmation_message' => 'Listo, tu cita quedo registrada. Te esperamos.',
            'appointment_status' => 'scheduled',
        ], $business->settings()->firstOrCreate([])->whatsapp_settings ?? []);
    }
}
