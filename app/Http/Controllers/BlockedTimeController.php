<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BlockedTime;
use App\Services\BookingEngine;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class BlockedTimeController extends Controller
{
    public function create(Request $request): View
    {
        $business = app('activeBusiness');
        $preview = $request->boolean('preview');
        $attributes = $preview ? $this->validated($request) : null;
        $impact = $attributes ? $this->impact($attributes) : collect();

        return view('blocked-times.create', [
            'business' => $business,
            'professionals' => $business->professionals()->where('is_active', true)->orderBy('name')->get(),
            'resources' => $business->resources()->where('is_active', true)->orderBy('name')->get(),
            'scopeOptions' => $this->scopeOptions(),
            'reasonOptions' => $this->reasonOptions(),
            'preview' => $preview,
            'impact' => $impact,
            'attributes' => $attributes ?? [
                'scope' => $request->input('scope', 'business'),
                'professional_id' => $request->input('professional_id'),
                'resource_id' => $request->input('resource_id'),
                'date' => $request->input('date', now($business->timezone)->toDateString()),
                'starts_at' => $request->input('starts_at', '08:00'),
                'ends_at' => $request->input('ends_at', '18:00'),
                'reason' => $request->input('reason', 'Otro'),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validated($request);
        $request->validate([
            'confirm_impact' => ['accepted'],
        ], [
            'confirm_impact.accepted' => 'Revisa el impacto y confirma antes de guardar el bloqueo.',
        ]);

        $startsAt = $this->startsAt($attributes);
        $endsAt = $this->endsAt($attributes);

        $blockedTime = app('activeBusiness')->blockedTimes()->create([
            'professional_id' => $attributes['scope'] === 'professional' ? $attributes['professional_id'] : null,
            'resource_id' => $attributes['scope'] === 'resource' ? $attributes['resource_id'] : null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'reason' => $attributes['reason'],
        ]);

        $message = 'Bloqueo guardado. TREBBIA recalculara disponibilidad para nuevas reservas.';

        return redirect()
            ->route('blocked-times.show', $blockedTime)
            ->with('status', $message);
    }

    public function show(BlockedTime $blockedTime, BookingEngine $booking): View
    {
        $this->authorizeTenant($blockedTime);

        $impact = $this->impact([
            'scope' => $blockedTime->professional_id ? 'professional' : ($blockedTime->resource_id ? 'resource' : 'business'),
            'professional_id' => $blockedTime->professional_id,
            'resource_id' => $blockedTime->resource_id,
            'date' => $blockedTime->starts_at->toDateString(),
            'starts_at' => $blockedTime->starts_at->format('H:i'),
            'ends_at' => $blockedTime->ends_at->format('H:i'),
            'reason' => $blockedTime->reason ?: 'Otro',
        ]);

        $suggestions = $impact->mapWithKeys(function (Appointment $appointment) use ($booking): array {
            return [$appointment->id => $this->suggestionsFor($appointment, $booking)->all()];
        });

        return view('blocked-times.show', [
            'business' => app('activeBusiness'),
            'blockedTime' => $blockedTime,
            'impact' => $impact,
            'suggestions' => $suggestions,
        ]);
    }

    private function validated(Request $request): array
    {
        $business = app('activeBusiness');

        $attributes = $request->validate([
            'scope' => ['required', Rule::in(array_keys($this->scopeOptions()))],
            'professional_id' => ['nullable', Rule::exists('professionals', 'id')->where('business_id', $business->id)->where('is_active', true)],
            'resource_id' => ['nullable', Rule::exists('resources', 'id')->where('business_id', $business->id)->where('is_active', true)],
            'date' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:H:i'],
            'ends_at' => ['required', 'date_format:H:i', 'after:starts_at'],
            'reason' => ['required', 'string', 'max:160'],
        ]);

        if ($attributes['scope'] === 'professional') {
            $request->validate(['professional_id' => ['required']]);
        }

        if ($attributes['scope'] === 'resource') {
            $request->validate(['resource_id' => ['required']]);
        }

        return $attributes;
    }

    private function impact(array $attributes)
    {
        $business = app('activeBusiness');
        $startsAt = $this->startsAt($attributes);
        $endsAt = $this->endsAt($attributes);

        return $business->appointments()
            ->with(['client', 'professional', 'service', 'resource'])
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED])
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->when($attributes['scope'] === 'professional', fn (Builder $query): Builder => $query->where('professional_id', $attributes['professional_id']))
            ->when($attributes['scope'] === 'resource', fn (Builder $query): Builder => $query->where('resource_id', $attributes['resource_id']))
            ->orderBy('starts_at')
            ->get();
    }

    private function startsAt(array $attributes): CarbonImmutable
    {
        return CarbonImmutable::parse($attributes['date'].' '.$attributes['starts_at'], app('activeBusiness')->timezone);
    }

    private function endsAt(array $attributes): CarbonImmutable
    {
        return CarbonImmutable::parse($attributes['date'].' '.$attributes['ends_at'], app('activeBusiness')->timezone);
    }

    private function scopeOptions(): array
    {
        return [
            'business' => 'Agenda general',
            'professional' => 'Profesional',
            'resource' => 'Recurso',
        ];
    }

    private function reasonOptions(): array
    {
        return [
            'Enfermedad',
            'Incapacidad',
            'Emergencia',
            'Vacaciones',
            'Reunion',
            'Bloqueo personal',
            'Mantenimiento',
            'Otro',
        ];
    }

    private function suggestionsFor(Appointment $appointment, BookingEngine $booking)
    {
        if (! $appointment->service || ! $appointment->professional_id) {
            return collect();
        }

        $business = app('activeBusiness');
        $baseDate = CarbonImmutable::parse($appointment->starts_at, $business->timezone);

        return collect(range(0, 6))
            ->flatMap(fn (int $days) => $booking->availableSlots(
                $business,
                $appointment->service,
                $appointment->professional_id,
                $baseDate->addDays($days),
                $appointment->resource_id,
            ))
            ->reject(fn (CarbonImmutable $slot): bool => $slot->equalTo(CarbonImmutable::parse($appointment->starts_at, $business->timezone)))
            ->take(3)
            ->values();
    }

    private function authorizeTenant(BlockedTime $blockedTime): void
    {
        abort_unless($blockedTime->business_id === app('activeBusiness')->id, 404);
    }
}
