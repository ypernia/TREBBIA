<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Business;
use App\Models\Client;
use App\Models\Service;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingEngine
{
    public function __construct(
        private AppointmentAvailabilityService $availability,
        private PlanEntitlements $entitlements,
    ) {}

    public function services(Business $business): Collection
    {
        return $business->services()
            ->with('professionals')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function service(Business $business, ?int $serviceId): ?Service
    {
        return $serviceId
            ? $business->services()->where('is_active', true)->find($serviceId)
            : null;
    }

    public function professionalsForService(Business $business, ?Service $service): Collection
    {
        if (! $service) {
            return collect();
        }

        $assignedIds = $service->professionals()->pluck('professionals.id');

        return $business->professionals()
            ->where('is_active', true)
            ->when($assignedIds->isNotEmpty(), fn ($query) => $query->whereIn('id', $assignedIds))
            ->orderBy('name')
            ->get();
    }

    public function professionalOptionsByService(Business $business, Collection $services): array
    {
        $activeProfessionals = $business->professionals()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return $services
            ->mapWithKeys(function (Service $service) use ($activeProfessionals): array {
                $assignedProfessionals = $service->professionals
                    ->where('is_active', true)
                    ->sortBy('name')
                    ->values()
                    ->map(fn ($professional): array => ['id' => $professional->id, 'name' => $professional->name]);

                $professionals = $assignedProfessionals->isNotEmpty()
                    ? $assignedProfessionals
                    : $activeProfessionals->map(fn ($professional): array => ['id' => $professional->id, 'name' => $professional->name]);

                return [$service->id => $professionals->values()->all()];
            })
            ->all();
    }

    public function availableSlots(Business $business, Service $service, int $professionalId, CarbonImmutable $date, ?int $resourceId = null): Collection
    {
        $settings = $business->settings()->firstOrCreate([]);
        $interval = $settings->slot_interval_minutes ?: 30;
        $schedule = $business->schedules()->where('weekday', $date->dayOfWeekIso)->first();

        if ($schedule && ($schedule->is_closed || ! $schedule->opens_at || ! $schedule->closes_at)) {
            return collect();
        }

        $opensAt = $schedule?->opens_at ?? '08:00:00';
        $closesAt = $schedule?->closes_at ?? '18:00:00';
        $cursor = CarbonImmutable::parse($date->format('Y-m-d').' '.$opensAt, $business->timezone);
        $endOfDay = CarbonImmutable::parse($date->format('Y-m-d').' '.$closesAt, $business->timezone);
        $slots = collect();

        while ($cursor->addMinutes($service->duration_minutes)->lessThanOrEqualTo($endOfDay)) {
            $endsAt = $cursor->addMinutes($service->duration_minutes);

            if ($this->availability->validate($business, $service, $cursor, $endsAt, $professionalId, $resourceId) === []) {
                $slots->push($cursor);
            }

            $cursor = $cursor->addMinutes($interval);
        }

        return $slots;
    }

    public function validateSlot(Business $business, Service $service, CarbonImmutable $startsAt, ?int $professionalId, ?int $resourceId = null, ?int $ignoreAppointmentId = null): array
    {
        return $this->availability->validate(
            $business,
            $service,
            $startsAt,
            $startsAt->addMinutes($service->duration_minutes),
            $professionalId,
            $resourceId,
            $ignoreAppointmentId,
        );
    }

    public function createAppointment(Business $business, array $attributes): Appointment
    {
        if (! $this->entitlements->can($business, 'appointment.create')) {
            throw ValidationException::withMessages(['plan' => 'Tu membresia no permite crear citas en este momento.']);
        }

        if (! $this->entitlements->hasCapacity($business, 'monthly_appointments')) {
            throw ValidationException::withMessages(['plan' => 'Alcanzaste el limite de citas mensuales de tu membresia.']);
        }

        return DB::transaction(function () use ($business, $attributes): Appointment {
            $service = $business->services()->where('is_active', true)->findOrFail($attributes['service_id']);
            $startsAt = $attributes['starts_at'] instanceof CarbonImmutable
                ? $attributes['starts_at']
                : CarbonImmutable::parse($attributes['starts_at'], $business->timezone);
            $endsAt = $startsAt->addMinutes($service->duration_minutes);
            $professionalId = (int) $attributes['professional_id'];
            $resourceId = ($attributes['resource_id'] ?? null) ? (int) $attributes['resource_id'] : null;
            $clientId = ($attributes['client_id'] ?? null) ? (int) $attributes['client_id'] : null;
            $branchId = ($attributes['branch_id'] ?? null) ? (int) $attributes['branch_id'] : null;

            $business->professionals()->where('is_active', true)->findOrFail($professionalId);

            if ($clientId) {
                $business->clients()->findOrFail($clientId);
            }

            if ($resourceId) {
                $business->resources()->where('is_active', true)->findOrFail($resourceId);
            }

            if ($branchId) {
                $business->branches()->where('is_active', true)->findOrFail($branchId);
            }

            $errors = $this->availability->validate($business, $service, $startsAt, $endsAt, $professionalId, $resourceId);

            if ($errors !== []) {
                throw ValidationException::withMessages(['starts_at' => $errors]);
            }

            return $business->appointments()->create([
                'branch_id' => $branchId ?: $business->branches()->where('is_main', true)->value('id'),
                'client_id' => $clientId,
                'service_id' => $service->id,
                'professional_id' => $professionalId,
                'resource_id' => $resourceId,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'status' => $attributes['status'] ?? 'scheduled',
                'source_channel' => $attributes['source_channel'] ?? Appointment::SOURCE_INTERNAL,
                'source_reference' => $attributes['source_reference'] ?? null,
                'source_metadata' => $attributes['source_metadata'] ?? null,
                'notes' => $attributes['notes'] ?? null,
            ]);
        });
    }

    public function findOrCreateClient(Business $business, array $attributes): Client
    {
        $query = $business->clients();

        if ($attributes['client_email'] ?? null) {
            $client = (clone $query)->where('email', $attributes['client_email'])->first();
        } elseif ($attributes['client_phone'] ?? null) {
            $client = (clone $query)->where('phone', $attributes['client_phone'])->first();
        } else {
            $client = null;
        }

        return $client ?: $business->clients()->create([
            'name' => $attributes['client_name'],
            'email' => $attributes['client_email'] ?? null,
            'phone' => $attributes['client_phone'] ?? null,
            'is_active' => true,
        ]);
    }
}
