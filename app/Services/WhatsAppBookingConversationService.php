<?php

namespace App\Services;

use App\Contracts\BookingIntentInterpreter;
use App\Models\Appointment;
use App\Models\Business;
use App\Models\Conversation;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

class WhatsAppBookingConversationService
{
    public function __construct(
        private ConversationManager $conversations,
        private BookingEngine $booking,
        private BookingIntentInterpreter $interpreter,
    ) {}

    public function replyFor(Business $business, Conversation $conversation, string $body): string
    {
        $state = $conversation->state?->state ?? 'idle';
        $data = $conversation->state?->data ?? [];
        $settings = $this->settings($business);
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

        if ($state === 'idle' && ($parsed['intent'] ?? null) !== 'booking') {
            return $settings['welcome_message'].' Puedes escribir "quiero agendar" para probar una reserva.';
        }

        $data = $this->mergeBookingData($data, $parsed);
        $service = $this->booking->service($business, $data['service_id'] ?? null);

        if (! $service) {
            $this->setConversationState($conversation, 'booking', 'awaiting_service', $data);

            return $state === 'awaiting_service'
                ? "No encontre ese servicio. Prueba escribiendo uno de estos nombres:\n\n".$this->serviceList($business)
                : "Claro. Que servicio deseas reservar?\n\n".$this->serviceList($business);
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

            return $state === 'awaiting_professional'
                ? 'No encontre ese profesional para el servicio seleccionado. Escribe el nombre tal como aparece en la lista.'
                : "Perfecto: {$service->name}. Con que profesional deseas atenderte?\n\n".$professionals->pluck('name')->implode("\n");
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

            return $settings['unavailable_message'];
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

    private function confirmAppointment(Business $business, Conversation $conversation, array $data, string $time): string
    {
        $contact = $conversation->whatsappContact;
        $settings = $this->settings($business);
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
                'status' => $settings['appointment_status'] ?? 'scheduled',
                'source_channel' => Appointment::SOURCE_WHATSAPP,
                'source_reference' => 'conversation:'.$conversation->id,
                'source_metadata' => ['simulated' => ! str_starts_with($settings['mode'] ?? 'link', 'cloud_api')],
            ]);
        } catch (ValidationException) {
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

        return $settings['confirmation_message']."\n\nServicio: {$appointment->service->name}\nFecha: ".$appointment->starts_at->format('d/m/Y')."\nHora: ".$appointment->starts_at->format('H:i')."\nProfesional: {$appointment->professional->name}";
    }

    private function setConversationState(Conversation $conversation, ?string $intent, string $step, array $data = []): void
    {
        $conversation->update([
            'intent' => $intent,
            'current_step' => $step,
        ]);
        $this->conversations->updateState($conversation, $step, $data, now()->addHours(24));
    }

    private function serviceList(Business $business): string
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

    private function availableSlotStrings(Business $business, $service, int $professionalId, CarbonImmutable $date): array
    {
        return $this->booking->availableSlots($business, $service, $professionalId, $date)
            ->take(8)
            ->map->format('H:i')
            ->values()
            ->all();
    }

    private function settings(Business $business): array
    {
        return array_merge([
            'enabled' => false,
            'phone' => $business->phone ?? '',
            'display_name' => $business->name,
            'welcome_message' => 'Hola, soy el asistente de reservas de '.$business->name.'. Te ayudo a encontrar un horario disponible.',
            'unavailable_message' => 'No encontre horarios disponibles para esa opcion. Probemos con otra fecha u horario.',
            'confirmation_message' => 'Listo, tu cita quedo registrada. Te esperamos.',
            'appointment_status' => 'scheduled',
            'mode' => 'link',
        ], $business->settings()->firstOrCreate([])->whatsapp_settings ?? []);
    }
}
