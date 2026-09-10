<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BlockedTime;
use App\Services\BookingEngine;
use App\Support\TimeInput;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
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
                'mode' => $request->input('mode', 'single'),
                'date' => $request->input('date', now($business->timezone)->toDateString()),
                'date_from' => $request->input('date_from', $request->input('date', now($business->timezone)->toDateString())),
                'date_to' => $request->input('date_to', $request->input('date', now($business->timezone)->toDateString())),
                'weekdays' => $request->input('weekdays', []),
                'starts_at' => TimeInput::normalize($request->input('starts_at', '08:00')),
                'ends_at' => TimeInput::normalize($request->input('ends_at', '18:00')),
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

        $blockedTimes = $this->slots($attributes)->map(function (array $slot) use ($attributes): BlockedTime {
            return app('activeBusiness')->blockedTimes()->create([
                'professional_id' => $attributes['scope'] === 'professional' ? $attributes['professional_id'] : null,
                'resource_id' => $attributes['scope'] === 'resource' ? $attributes['resource_id'] : null,
                'starts_at' => $slot['starts_at'],
                'ends_at' => $slot['ends_at'],
                'reason' => $attributes['reason'],
            ]);
        });

        $blockedTime = $blockedTimes->firstOrFail();
        $message = $blockedTimes->count() === 1
            ? 'Bloqueo guardado. TREBBIA recalculara disponibilidad para nuevas reservas.'
            : "{$blockedTimes->count()} bloqueos guardados. TREBBIA recalculara disponibilidad para nuevas reservas.";

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
            'mode' => 'single',
            'date' => $blockedTime->starts_at->toDateString(),
            'date_from' => $blockedTime->starts_at->toDateString(),
            'date_to' => $blockedTime->starts_at->toDateString(),
            'weekdays' => [],
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
            'mode' => ['required', Rule::in(['single', 'multiple'])],
            'date' => ['nullable', 'required_if:mode,single', 'date'],
            'date_from' => ['nullable', 'required_if:mode,multiple', 'date'],
            'date_to' => ['nullable', 'required_if:mode,multiple', 'date', 'after_or_equal:date_from'],
            'weekdays' => ['nullable', 'array'],
            'weekdays.*' => ['integer', 'between:1,7'],
            'starts_at' => ['required', TimeInput::VALIDATION_RULE],
            'ends_at' => ['required', TimeInput::VALIDATION_RULE, 'after:starts_at'],
            'reason' => ['required', 'string', 'max:160'],
        ], [
            'starts_at.required' => 'Indica la hora inicial del bloqueo.',
            'starts_at.date_format' => 'La hora inicial debe ser valida, por ejemplo 08:00.',
            'ends_at.required' => 'Indica la hora final del bloqueo.',
            'ends_at.date_format' => 'La hora final debe ser valida, por ejemplo 18:00.',
            'ends_at.after' => 'La hora final debe ser posterior a la hora inicial.',
            'date.required_if' => 'Indica la fecha del bloqueo.',
            'date_from.required_if' => 'Indica la fecha inicial.',
            'date_to.required_if' => 'Indica la fecha final.',
            'date_to.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ], [
            'starts_at' => 'hora inicial',
            'ends_at' => 'hora final',
            'date' => 'fecha',
            'date_from' => 'fecha inicial',
            'date_to' => 'fecha final',
            'weekdays' => 'dias',
            'professional_id' => 'profesional',
            'resource_id' => 'recurso',
            'scope' => 'tipo de bloqueo',
            'reason' => 'motivo',
        ]);

        if ($attributes['scope'] === 'professional') {
            $request->validate(['professional_id' => ['required']]);
        }

        if ($attributes['scope'] === 'resource') {
            $request->validate(['resource_id' => ['required']]);
        }

        if ($attributes['mode'] === 'multiple') {
            $request->validate([
                'weekdays' => ['required', 'array', 'min:1'],
            ], [
                'weekdays.required' => 'Selecciona al menos un dia para aplicar el bloqueo.',
                'weekdays.min' => 'Selecciona al menos un dia para aplicar el bloqueo.',
            ]);

            $from = CarbonImmutable::parse($attributes['date_from'], $business->timezone)->startOfDay();
            $to = CarbonImmutable::parse($attributes['date_to'], $business->timezone)->startOfDay();

            if ($from->diffInDays($to) > 90) {
                validator([], [])->after(fn ($validator) => $validator->errors()->add('date_to', 'El rango maximo permitido es de 90 dias.'))->validate();
            }

            if ($this->datesFor($attributes)->isEmpty()) {
                validator([], [])->after(fn ($validator) => $validator->errors()->add('weekdays', 'El rango seleccionado no contiene los dias marcados.'))->validate();
            }
        }

        return $attributes;
    }

    private function impact(array $attributes)
    {
        $business = app('activeBusiness');
        $slots = $this->slots($attributes);

        return $business->appointments()
            ->with(['client', 'professional', 'service', 'resource'])
            ->whereIn('status', [Appointment::STATUS_SCHEDULED, Appointment::STATUS_CONFIRMED])
            ->where(function (Builder $query) use ($slots): void {
                $slots->each(function (array $slot) use ($query): void {
                    $query->orWhere(function (Builder $query) use ($slot): void {
                        $query
                            ->where('starts_at', '<', $slot['ends_at'])
                            ->where('ends_at', '>', $slot['starts_at']);
                    });
                });
            })
            ->when($attributes['scope'] === 'professional', fn (Builder $query): Builder => $query->where('professional_id', $attributes['professional_id']))
            ->when($attributes['scope'] === 'resource', fn (Builder $query): Builder => $query->where('resource_id', $attributes['resource_id']))
            ->orderBy('starts_at')
            ->get();
    }

    private function startsAt(array $attributes): CarbonImmutable
    {
        return CarbonImmutable::parse($attributes['date'].' '.TimeInput::normalize($attributes['starts_at']), app('activeBusiness')->timezone);
    }

    private function endsAt(array $attributes): CarbonImmutable
    {
        return CarbonImmutable::parse($attributes['date'].' '.TimeInput::normalize($attributes['ends_at']), app('activeBusiness')->timezone);
    }

    private function slots(array $attributes): Collection
    {
        $business = app('activeBusiness');
        $dates = $attributes['mode'] === 'multiple'
            ? $this->datesFor($attributes)
            : collect([$attributes['date']]);

        return $dates->map(fn (string $date): array => [
            'starts_at' => CarbonImmutable::parse($date.' '.TimeInput::normalize($attributes['starts_at']), $business->timezone),
            'ends_at' => CarbonImmutable::parse($date.' '.TimeInput::normalize($attributes['ends_at']), $business->timezone),
        ]);
    }

    private function datesFor(array $attributes): Collection
    {
        $business = app('activeBusiness');
        $weekdays = collect($attributes['weekdays'] ?? [])->map(fn ($weekday): int => (int) $weekday);
        $cursor = CarbonImmutable::parse($attributes['date_from'], $business->timezone)->startOfDay();
        $end = CarbonImmutable::parse($attributes['date_to'], $business->timezone)->startOfDay();
        $dates = collect();

        while ($cursor->lessThanOrEqualTo($end)) {
            if ($weekdays->contains($cursor->dayOfWeekIso)) {
                $dates->push($cursor->toDateString());
            }

            $cursor = $cursor->addDay();
        }

        return $dates;
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
