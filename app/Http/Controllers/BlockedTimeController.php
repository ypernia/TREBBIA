<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
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

        app('activeBusiness')->blockedTimes()->create([
            'professional_id' => $attributes['scope'] === 'professional' ? $attributes['professional_id'] : null,
            'resource_id' => $attributes['scope'] === 'resource' ? $attributes['resource_id'] : null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'reason' => $attributes['reason'],
        ]);

        $message = 'Bloqueo guardado. TREBBIA recalculara disponibilidad para nuevas reservas.';

        return redirect()
            ->route('agenda.index', ['date' => $startsAt->toDateString()])
            ->with('status', $message);
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
}
