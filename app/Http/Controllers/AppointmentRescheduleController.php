<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\AppointmentNotificationService;
use App\Services\BookingEngine;
use App\Support\TimeInput;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AppointmentRescheduleController extends Controller
{
    public function update(Request $request, Appointment $appointment, BookingEngine $booking, AppointmentNotificationService $notifications): RedirectResponse
    {
        $this->authorizeTenant($appointment);

        $attributes = $request->validate([
            'date' => ['required', 'date'],
            'starts_at' => ['required', TimeInput::VALIDATION_RULE],
            'return_to' => ['nullable', 'url'],
        ], [
            'starts_at.required' => 'Indica la nueva hora de la cita.',
            'starts_at.date_format' => 'La nueva hora debe ser valida, por ejemplo 09:00.',
        ], [
            'starts_at' => 'nueva hora',
            'date' => 'fecha',
        ]);

        $business = app('activeBusiness');
        $startsAt = CarbonImmutable::parse($attributes['date'].' '.TimeInput::normalize($attributes['starts_at']), $business->timezone);
        $errors = $booking->validateSlot(
            $business,
            $appointment->service,
            $startsAt,
            $appointment->professional_id,
            $appointment->resource_id,
            $appointment->id,
        );

        if ($errors !== []) {
            throw ValidationException::withMessages(['starts_at' => $errors]);
        }

        $metadata = $appointment->source_metadata ?? [];
        $metadata['last_rescheduled_at'] = now()->toISOString();
        $metadata['last_rescheduled_by'] = $request->user()->id;
        $metadata['reschedule_contact_pending'] = true;
        $metadata['reschedule_contact_status'] = Appointment::CONTACT_PENDING;
        $previousSchedule = [
            'starts_at' => CarbonImmutable::parse($appointment->starts_at, $business->timezone),
            'ends_at' => CarbonImmutable::parse($appointment->ends_at, $business->timezone),
        ];

        $appointment->update([
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes($appointment->service->duration_minutes),
            'source_metadata' => $metadata,
        ]);

        $notifications->appointmentRescheduled($appointment, $previousSchedule);

        return redirect(($attributes['return_to'] ?? null) ?: route('agenda.index', ['date' => $startsAt->toDateString()]))
            ->with('status', 'Cita reprogramada. Contacta al cliente para confirmar el cambio.');
    }

    public function markContactPending(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeTenant($appointment);

        $attributes = $request->validate([
            'return_to' => ['nullable', 'url'],
        ]);

        $metadata = $appointment->source_metadata ?? [];
        $metadata['reschedule_contact_pending'] = true;
        $metadata['reschedule_contact_status'] = Appointment::CONTACT_PENDING;
        $metadata['reschedule_contact_marked_at'] = now()->toISOString();
        $metadata['reschedule_contact_marked_by'] = $request->user()->id;

        $appointment->update(['source_metadata' => $metadata]);

        return redirect(($attributes['return_to'] ?? null) ?: route('agenda.index'))
            ->with('status', 'Cita marcada como pendiente por contactar.');
    }

    public function updateContactStatus(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeTenant($appointment);

        $attributes = $request->validate([
            'contact_status' => ['required', Rule::in(array_keys(Appointment::contactStatusLabels()))],
            'return_to' => ['nullable', 'url'],
        ]);

        $metadata = $appointment->source_metadata ?? [];
        $metadata['reschedule_contact_status'] = $attributes['contact_status'];
        $metadata['reschedule_contact_pending'] = $attributes['contact_status'] !== Appointment::CONTACT_CONFIRMED;
        $metadata['reschedule_contact_updated_at'] = now()->toISOString();
        $metadata['reschedule_contact_updated_by'] = $request->user()->id;

        $appointment->update(['source_metadata' => $metadata]);

        return redirect(($attributes['return_to'] ?? null) ?: route('agenda.index'))
            ->with('status', 'Estado de contacto actualizado.');
    }

    private function authorizeTenant(Appointment $appointment): void
    {
        abort_unless($appointment->business_id === app('activeBusiness')->id, 404);
    }
}
