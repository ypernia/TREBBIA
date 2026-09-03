<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Service;
use App\Services\AppointmentAvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
    public function __construct(private AppointmentAvailabilityService $availability) {}

    public function show(Request $request, Business $business): View
    {
        abort_unless($this->publicBookingEnabled($business), 404);

        $settings = $business->settings()->firstOrCreate([]);
        $selectedService = $this->selectedService($business, $request);
        $professionals = $this->professionalsForService($business, $selectedService);
        $selectedProfessional = $professionals->firstWhere('id', $request->integer('professional_id'));
        $date = CarbonImmutable::parse($request->input('date', now($business->timezone)->addDay()->toDateString()), $business->timezone);

        return view('public-booking.show', [
            'business' => $business,
            'settings' => $settings,
            'services' => $services = $business->services()->with('professionals')->where('is_active', true)->orderBy('name')->get(),
            'selectedService' => $selectedService,
            'professionals' => $professionals,
            'professionalOptionsByService' => $this->professionalOptionsByService($business, $services),
            'selectedProfessional' => $selectedProfessional,
            'date' => $date,
            'availableSlots' => $selectedService && $selectedProfessional
                ? $this->availableSlots($business, $selectedService, $selectedProfessional->id, $date)
                : collect(),
        ]);
    }

    public function store(Request $request, Business $business): RedirectResponse
    {
        abort_unless($this->publicBookingEnabled($business), 404);

        $attributes = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where('business_id', $business->id)->where('is_active', true)],
            'professional_id' => ['required', Rule::exists('professionals', 'id')->where('business_id', $business->id)->where('is_active', true)],
            'date' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:H:i'],
            'client_name' => ['required', 'string', 'max:140'],
            'client_email' => ['nullable', 'email', 'max:180'],
            'client_phone' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:800'],
        ]);

        $service = $business->services()->where('is_active', true)->findOrFail($attributes['service_id']);
        $startsAt = CarbonImmutable::parse($attributes['date'].' '.$attributes['starts_at'], $business->timezone);
        $endsAt = $startsAt->addMinutes($service->duration_minutes);
        $errors = $this->availability->validate($business, $service, $startsAt, $endsAt, (int) $attributes['professional_id'], null);

        if ($errors !== []) {
            validator([], [])->after(function (Validator $validator) use ($errors): void {
                foreach ($errors as $error) {
                    $validator->errors()->add('starts_at', $error);
                }
            })->validate();
        }

        $client = $this->findOrCreateClient($business, $attributes);
        $settings = $business->settings()->firstOrCreate([]);
        $requiresConfirmation = (bool) ($settings->public_booking_settings['require_manual_confirmation'] ?? true);

        $appointment = $business->appointments()->create([
            'branch_id' => $business->branches()->where('is_main', true)->value('id'),
            'client_id' => $client->id,
            'service_id' => $service->id,
            'professional_id' => $attributes['professional_id'],
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $requiresConfirmation ? 'scheduled' : 'confirmed',
            'notes' => $attributes['notes'] ?? null,
        ]);

        return redirect()->route('public-booking.confirmation', [$business->slug, 'appointment' => $appointment->id]);
    }

    public function confirmation(Business $business, int $appointment): View
    {
        abort_unless($this->publicBookingEnabled($business), 404);

        $appointment = $business->appointments()
            ->with(['client', 'professional', 'service'])
            ->whereKey($appointment)
            ->firstOrFail();

        return view('public-booking.confirmation', [
            'business' => $business,
            'appointment' => $appointment,
        ]);
    }

    private function selectedService(Business $business, Request $request): ?Service
    {
        $serviceId = $request->integer('service_id');

        return $serviceId
            ? $business->services()->where('is_active', true)->find($serviceId)
            : null;
    }

    private function professionalsForService(Business $business, ?Service $service)
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

    private function professionalOptionsByService(Business $business, $services): array
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

    private function availableSlots(Business $business, Service $service, int $professionalId, CarbonImmutable $date)
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

            if ($this->availability->validate($business, $service, $cursor, $endsAt, $professionalId, null) === []) {
                $slots->push($cursor);
            }

            $cursor = $cursor->addMinutes($interval);
        }

        return $slots;
    }

    private function findOrCreateClient(Business $business, array $attributes)
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

    private function publicBookingEnabled(Business $business): bool
    {
        $settings = $business->settings()->firstOrCreate([]);

        return $business->status === 'active'
            && (bool) ($settings->public_booking_settings['allow_public_booking'] ?? false);
    }
}
