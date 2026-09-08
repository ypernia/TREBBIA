<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Services\PlanEntitlements;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProfessionalController extends Controller
{
    public function index()
    {
        $business = app('activeBusiness');

        return view('professionals.index', [
            'business' => $business,
            'professionals' => $business->professionals()->with(['branch', 'services'])->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return view('professionals.form', [
            'professional' => new Professional,
            'business' => app('activeBusiness'),
            'branches' => app('activeBusiness')->branches()->orderBy('name')->get(),
            'services' => app('activeBusiness')->services()->where('is_active', true)->orderBy('name')->get(),
            'schedules' => collect(),
            'weekdays' => $this->weekdays(),
        ]);
    }

    public function store(Request $request)
    {
        $business = app('activeBusiness');
        abort_unless(app(PlanEntitlements::class)->can($business, 'professional.manage'), 403);

        if (! app(PlanEntitlements::class)->hasCapacity($business, 'professionals')) {
            return back()->withErrors(['name' => 'Alcanzaste el limite de profesionales de tu membresia.'])->withInput();
        }

        $attributes = $this->validated($request);
        $professional = $business->professionals()->create($attributes['professional']);
        $this->syncServices($professional, $attributes['service_ids']);
        $this->syncSchedules($professional, $attributes['schedule']);

        return redirect()->route('profesionales.index')->with('status', 'Profesional creado.');
    }

    public function edit(Professional $profesionale)
    {
        $this->authorizeTenant($profesionale);

        return view('professionals.form', [
            'professional' => $profesionale,
            'business' => app('activeBusiness'),
            'branches' => app('activeBusiness')->branches()->orderBy('name')->get(),
            'services' => app('activeBusiness')->services()->where('is_active', true)->orderBy('name')->get(),
            'schedules' => $profesionale->schedules()->get()->keyBy('weekday'),
            'weekdays' => $this->weekdays(),
        ]);
    }

    public function update(Request $request, Professional $profesionale)
    {
        $this->authorizeTenant($profesionale);
        $attributes = $this->validated($request);
        $profesionale->update($attributes['professional']);
        $this->syncServices($profesionale, $attributes['service_ids']);
        $this->syncSchedules($profesionale, $attributes['schedule']);

        return redirect()->route('profesionales.index')->with('status', 'Profesional actualizado.');
    }

    public function destroy(Professional $profesionale)
    {
        $this->authorizeTenant($profesionale);
        $profesionale->delete();

        return redirect()->route('profesionales.index')->with('status', 'Profesional archivado.');
    }

    private function validated(Request $request): array
    {
        $business = app('activeBusiness');

        $attributes = $request->validate([
            'branch_id' => ['nullable', 'integer', 'exists:branches,id', function ($attribute, $value, $fail) use ($business): void {
                if ($value && ! $business->branches()->whereKey($value)->exists()) {
                    $fail('La sede seleccionada no pertenece a este negocio.');
                }
            }],
            'name' => ['required', 'string', 'max:140'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:60'],
            'title' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
            'service_ids' => ['nullable', 'array'],
            'service_ids.*' => [Rule::exists('services', 'id')->where('business_id', $business->id)],
            'schedule' => ['nullable', 'array'],
            'schedule.*.starts_at' => ['nullable', 'date_format:H:i,H:i:s'],
            'schedule.*.ends_at' => ['nullable', 'date_format:H:i,H:i:s'],
            'schedule.*.is_closed' => ['nullable', 'boolean'],
        ], $this->scheduleMessages(), $this->scheduleAttributes());

        foreach ($this->weekdays() as $weekday => $label) {
            if (! array_key_exists($weekday, $attributes['schedule'] ?? [])) {
                continue;
            }

            $row = $attributes['schedule'][$weekday] ?? [];
            $isClosed = (bool) ($row['is_closed'] ?? false);

            $request->validate([
                "schedule.{$weekday}.ends_at" => [$isClosed ? 'nullable' : 'required', 'date_format:H:i,H:i:s', "after:schedule.{$weekday}.starts_at"],
                "schedule.{$weekday}.starts_at" => [$isClosed ? 'nullable' : 'required', 'date_format:H:i,H:i:s'],
            ], $this->scheduleMessages(), $this->scheduleAttributes());
        }

        return [
            'professional' => [
                'branch_id' => $attributes['branch_id'] ?? null,
                'name' => $attributes['name'],
                'email' => $attributes['email'] ?? null,
                'phone' => $attributes['phone'] ?? null,
                'title' => $attributes['title'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ],
            'service_ids' => collect($attributes['service_ids'] ?? [])->map(fn ($id) => (int) $id)->all(),
            'schedule' => $attributes['schedule'] ?? [],
        ];
    }

    private function authorizeTenant(Professional $professional): void
    {
        abort_unless($professional->business_id === app('activeBusiness')->id, 404);
    }

    private function syncServices(Professional $professional, array $serviceIds): void
    {
        $professional->services()->syncWithPivotValues($serviceIds, ['business_id' => app('activeBusiness')->id]);
    }

    private function syncSchedules(Professional $professional, array $schedule): void
    {
        foreach ($this->weekdays() as $weekday => $label) {
            if (! array_key_exists($weekday, $schedule)) {
                continue;
            }

            $row = $schedule[$weekday] ?? [];
            $isClosed = (bool) ($row['is_closed'] ?? false);

            ProfessionalSchedule::updateOrCreate(
                ['business_id' => app('activeBusiness')->id, 'professional_id' => $professional->id, 'weekday' => $weekday],
                [
                    'starts_at' => $isClosed ? null : $this->normalizeTime($row['starts_at']),
                    'ends_at' => $isClosed ? null : $this->normalizeTime($row['ends_at']),
                    'is_closed' => $isClosed,
                ],
            );
        }
    }

    private function weekdays(): array
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
            7 => 'Domingo',
        ];
    }

    private function scheduleAttributes(): array
    {
        $attributes = [];

        foreach ($this->weekdays() as $weekday => $label) {
            $attributes["schedule.{$weekday}.starts_at"] = "{$label} - hora de inicio";
            $attributes["schedule.{$weekday}.ends_at"] = "{$label} - hora de fin";
            $attributes["schedule.{$weekday}.is_closed"] = "{$label} cerrado";
        }

        return $attributes;
    }

    private function scheduleMessages(): array
    {
        return [
            'schedule.*.starts_at.required' => ':attribute es obligatoria si el dia esta abierto.',
            'schedule.*.ends_at.required' => ':attribute es obligatoria si el dia esta abierto.',
            'schedule.*.starts_at.date_format' => ':attribute debe ser una hora valida, por ejemplo 08:00.',
            'schedule.*.ends_at.date_format' => ':attribute debe ser una hora valida, por ejemplo 18:00.',
            'schedule.*.ends_at.after' => ':attribute debe ser posterior a la hora de inicio.',
        ];
    }

    private function normalizeTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }
}
