<?php

namespace App\Http\Controllers;

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
    ) {}

    public function index(Request $request): View
    {
        $business = app('activeBusiness');
        $conversation = $this->selectedConversation($request);

        return view('whatsapp-simulator.index', [
            'business' => $business,
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
        $message = $this->normalizeText($body);

        if (str_contains($message, 'cancelar') || str_contains($message, 'reiniciar')) {
            $this->conversations->updateState($conversation, 'idle');
            $conversation->update(['intent' => null, 'current_step' => 'idle']);

            return 'Listo. Reinicie el flujo. Escribe "quiero agendar" para iniciar una reserva.';
        }

        if ($state === 'idle' && ! Str::contains($message, ['agendar', 'reservar', 'cita'])) {
            return 'Hola, soy el simulador de WhatsApp de TREBBIA. Puedes escribir "quiero agendar" para probar una reserva.';
        }

        if ($state === 'idle') {
            $this->setConversationState($conversation, 'booking', 'awaiting_service');

            return "Claro. Que servicio deseas reservar?\n\n".$this->serviceList($business);
        }

        if ($state === 'awaiting_service') {
            $service = $this->matchService($business, $body);

            if (! $service) {
                return "No encontre ese servicio. Prueba escribiendo uno de estos nombres:\n\n".$this->serviceList($business);
            }

            $professionals = $this->booking->professionalsForService($business, $service);
            $this->setConversationState($conversation, 'booking', 'awaiting_professional', ['service_id' => $service->id]);

            if ($professionals->isEmpty()) {
                return 'Ese servicio no tiene profesionales activos asociados. Configuralos en TREBBIA antes de reservar.';
            }

            return "Perfecto: {$service->name}. Con que profesional deseas atenderte?\n\n".$professionals->pluck('name')->implode("\n");
        }

        if ($state === 'awaiting_professional') {
            $service = $this->booking->service($business, $data['service_id'] ?? null);
            $professional = $service ? $this->matchProfessional($business, $service, $body) : null;

            if (! $service || ! $professional) {
                return 'No encontre ese profesional para el servicio seleccionado. Escribe el nombre tal como aparece en la lista.';
            }

            $this->setConversationState($conversation, 'booking', 'awaiting_date', [
                'service_id' => $service->id,
                'professional_id' => $professional->id,
            ]);

            return "Bien. Que fecha deseas? Puedes escribir manana o una fecha como 2026-09-07.";
        }

        if ($state === 'awaiting_date') {
            $service = $this->booking->service($business, $data['service_id'] ?? null);
            $date = $this->parseDate($business, $body);

            if (! $service || ! $date) {
                return 'No pude entender la fecha. Escribe por ejemplo: manana o 2026-09-07.';
            }

            $slots = $this->booking->availableSlots($business, $service, (int) $data['professional_id'], $date)
                ->take(6)
                ->map->format('H:i')
                ->values()
                ->all();

            if ($slots === []) {
                return 'No encontre horarios disponibles para esa fecha. Prueba con otro dia.';
            }

            $this->setConversationState($conversation, 'booking', 'awaiting_time', [
                'service_id' => $service->id,
                'professional_id' => $data['professional_id'],
                'date' => $date->toDateString(),
                'slots' => $slots,
            ]);

            return "Tengo estos horarios disponibles:\n\n".implode("\n", $slots)."\n\nEscribe el horario que deseas confirmar.";
        }

        if ($state === 'awaiting_time') {
            $time = $this->parseTime($body);
            $slots = $data['slots'] ?? [];

            if (! $time || ! in_array($time, $slots, true)) {
                return 'Ese horario no esta en la lista disponible. Escribe uno exactamente como aparece, por ejemplo 09:00.';
            }

            return $this->confirmAppointment($business, $conversation, $data, $time);
        }

        return 'Ya tengo una conversacion abierta. Escribe "reiniciar" si quieres empezar de nuevo.';
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
                'status' => 'scheduled',
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

        return "Listo. Tu cita quedo registrada.\n\nServicio: {$appointment->service->name}\nFecha: ".$appointment->starts_at->format('d/m/Y')."\nHora: ".$appointment->starts_at->format('H:i')."\nProfesional: {$appointment->professional->name}";
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

    private function matchService($business, string $body)
    {
        $message = $this->normalizeText($body);

        return $this->booking->services($business)->first(
            fn ($service) => str_contains($message, $this->normalizeText($service->name))
                || str_contains($this->normalizeText($service->name), $message)
        );
    }

    private function matchProfessional($business, $service, string $body)
    {
        $message = $this->normalizeText($body);

        return $this->booking->professionalsForService($business, $service)->first(
            fn ($professional) => str_contains($message, $this->normalizeText($professional->name))
                || str_contains($this->normalizeText($professional->name), $message)
        );
    }

    private function parseDate($business, string $body): ?CarbonImmutable
    {
        $message = $this->normalizeText($body);

        if (str_contains($message, 'manana')) {
            return CarbonImmutable::now($business->timezone)->addDay()->startOfDay();
        }

        if (preg_match('/\d{4}-\d{2}-\d{2}/', $body, $match)) {
            return CarbonImmutable::parse($match[0], $business->timezone);
        }

        return null;
    }

    private function parseTime(string $body): ?string
    {
        if (! preg_match('/\b([01]?\d|2[0-3]):([0-5]\d)\b/', $body, $match)) {
            return null;
        }

        return str_pad($match[1], 2, '0', STR_PAD_LEFT).':'.$match[2];
    }

    private function normalizeText(string $value): string
    {
        $value = Str::lower(trim($value));

        return str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n'],
            $value,
        );
    }

    private function normalizePhone(string $value): string
    {
        return preg_replace('/[^\d+]/', '', $value) ?: $value;
    }
}
