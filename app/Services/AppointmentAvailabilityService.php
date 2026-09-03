<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Business;
use App\Models\Resource;
use App\Models\Service;
use Carbon\CarbonInterface;

class AppointmentAvailabilityService
{
    public function validate(Business $business, Service $service, CarbonInterface $startsAt, CarbonInterface $endsAt, ?int $professionalId, ?int $resourceId, ?int $ignoreAppointmentId = null): array
    {
        $errors = [];

        if ($endsAt->lessThanOrEqualTo($startsAt)) {
            $errors[] = 'La hora final debe ser posterior a la hora inicial.';
        }

        if ($startsAt->diffInMinutes($endsAt) < $service->duration_minutes) {
            $errors[] = 'La cita no cubre la duracion configurada del servicio.';
        }

        if (! $this->isInsideBusinessSchedule($business, $startsAt, $endsAt)) {
            $errors[] = 'La cita queda fuera del horario general del negocio.';
        }

        if ($professionalId && $this->overlaps($business, $startsAt, $endsAt, 'professional_id', $professionalId, $ignoreAppointmentId)) {
            $errors[] = 'El profesional ya tiene una cita en ese horario.';
        }

        if ($resourceId && Resource::whereKey($resourceId)->where('business_id', $business->id)->exists()
            && $this->overlaps($business, $startsAt, $endsAt, 'resource_id', $resourceId, $ignoreAppointmentId)) {
            $errors[] = 'El recurso ya esta reservado en ese horario.';
        }

        return $errors;
    }

    public function hasConflicts(Business $business, CarbonInterface $startsAt, CarbonInterface $endsAt, ?int $professionalId, ?int $resourceId, ?int $ignoreAppointmentId = null): bool
    {
        return ($professionalId && $this->overlaps($business, $startsAt, $endsAt, 'professional_id', $professionalId, $ignoreAppointmentId))
            || ($resourceId && $this->overlaps($business, $startsAt, $endsAt, 'resource_id', $resourceId, $ignoreAppointmentId));
    }

    private function isInsideBusinessSchedule(Business $business, CarbonInterface $startsAt, CarbonInterface $endsAt): bool
    {
        $weekday = $startsAt->dayOfWeekIso;
        $schedule = $business->schedules()->where('weekday', $weekday)->first();

        if (! $schedule) {
            return true;
        }

        if ($schedule->is_closed || ! $schedule->opens_at || ! $schedule->closes_at) {
            return false;
        }

        return $startsAt->format('H:i:s') >= $schedule->opens_at
            && $endsAt->format('H:i:s') <= $schedule->closes_at;
    }

    private function overlaps(Business $business, CarbonInterface $startsAt, CarbonInterface $endsAt, string $column, int $id, ?int $ignoreAppointmentId): bool
    {
        return Appointment::query()
            ->where('business_id', $business->id)
            ->where($column, $id)
            ->whereNotIn('status', ['cancelled', 'completed'])
            ->when($ignoreAppointmentId, fn ($query) => $query->whereKeyNot($ignoreAppointmentId))
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->exists();
    }
}
