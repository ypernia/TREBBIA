<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\BookingEngine;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentRescheduleController extends Controller
{
    public function update(Request $request, Appointment $appointment, BookingEngine $booking): RedirectResponse
    {
        $this->authorizeTenant($appointment);

        $attributes = $request->validate([
            'date' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:H:i'],
            'return_to' => ['nullable', 'url'],
        ]);

        $business = app('activeBusiness');
        $startsAt = CarbonImmutable::parse($attributes['date'].' '.$attributes['starts_at'], $business->timezone);
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

        $appointment->update([
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->addMinutes($appointment->service->duration_minutes),
            'source_metadata' => $metadata,
        ]);

        return redirect($attributes['return_to'] ?: route('agenda.index', ['date' => $startsAt->toDateString()]))
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
        $metadata['reschedule_contact_marked_at'] = now()->toISOString();
        $metadata['reschedule_contact_marked_by'] = $request->user()->id;

        $appointment->update(['source_metadata' => $metadata]);

        return redirect($attributes['return_to'] ?: route('agenda.index'))
            ->with('status', 'Cita marcada como pendiente por contactar.');
    }

    private function authorizeTenant(Appointment $appointment): void
    {
        abort_unless($appointment->business_id === app('activeBusiness')->id, 404);
    }
}
