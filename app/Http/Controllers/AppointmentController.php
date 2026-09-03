<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Services\AppointmentAvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private AppointmentAvailabilityService $availability) {}

    public function index(Request $request): View
    {
        $business = app('activeBusiness');
        $date = CarbonImmutable::parse($request->input('date', today()->toDateString()), $business->timezone);
        $view = $request->input('view') === 'week' ? 'week' : 'day';
        $filters = [
            'professional_id' => $request->integer('professional_id') ?: null,
            'service_id' => $request->integer('service_id') ?: null,
            'status' => $request->input('status'),
        ];
        $weekStart = $date->startOfWeek();
        $weekEnd = $date->endOfWeek();

        $appointmentsQuery = $business->appointments()
            ->with(['client', 'professional', 'service', 'resource'])
            ->tap(fn (Builder $query) => $this->applyFilters($query, $filters));

        $appointments = $view === 'week'
            ? (clone $appointmentsQuery)->whereBetween('starts_at', [$weekStart, $weekEnd])->orderBy('starts_at')->get()
            : (clone $appointmentsQuery)->whereDate('starts_at', $date)->orderBy('starts_at')->get();

        return view('appointments.index', [
            'business' => $business,
            'date' => $date,
            'view' => $view,
            'filters' => $filters,
            'weekStart' => $weekStart,
            'weekDays' => collect(range(0, 6))->map(fn (int $days) => $weekStart->addDays($days)),
            'appointments' => $appointments,
            'appointmentsByDay' => $appointments->groupBy(fn (Appointment $appointment) => $appointment->starts_at->toDateString()),
            'upcoming' => $business->appointments()
                ->with(['client', 'professional', 'service'])
                ->where('starts_at', '>=', now())
                ->tap(fn (Builder $query) => $this->applyFilters($query, $filters))
                ->orderBy('starts_at')
                ->take(8)
                ->get(),
            'professionals' => $business->professionals()->with('services')->where('is_active', true)->orderBy('name')->get(),
            'services' => $business->services()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => $this->statuses(),
        ]);
    }

    public function create(Request $request): View
    {
        return view('appointments.form', $this->formData(new Appointment, $request));
    }

    public function store(Request $request): RedirectResponse
    {
        $attributes = $this->validated($request);

        app('activeBusiness')->appointments()->create($attributes);

        return redirect()->route('agenda.index', ['date' => $attributes['starts_at']->toDateString()])
            ->with('status', 'Cita creada.');
    }

    public function edit(Appointment $appointment): View
    {
        $this->authorizeTenant($appointment);

        return view('appointments.form', $this->formData($appointment, request()));
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeTenant($appointment);
        $attributes = $this->validated($request, $appointment);
        $appointment->update($attributes);

        return redirect()->route('agenda.index', ['date' => $attributes['starts_at']->toDateString()])
            ->with('status', 'Cita actualizada.');
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorizeTenant($appointment);
        $appointment->delete();

        return redirect()->route('agenda.index')->with('status', 'Cita archivada.');
    }

    private function formData(Appointment $appointment, Request $request): array
    {
        $business = app('activeBusiness');

        return [
            'appointment' => $appointment,
            'business' => $business,
            'clients' => $business->clients()->orderBy('name')->get(),
            'professionals' => $business->professionals()->where('is_active', true)->orderBy('name')->get(),
            'services' => $business->services()->where('is_active', true)->orderBy('name')->get(),
            'resources' => $business->resources()->where('is_active', true)->orderBy('name')->get(),
            'branches' => $business->branches()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => $this->statuses(),
            'availabilityWarnings' => $this->availabilityWarnings($request, $appointment),
        ];
    }

    private function validated(Request $request, ?Appointment $appointment = null): array
    {
        $business = app('activeBusiness');

        $attributes = $request->validate([
            'client_id' => ['nullable', Rule::exists('clients', 'id')->where('business_id', $business->id)],
            'professional_id' => ['required', Rule::exists('professionals', 'id')->where('business_id', $business->id)],
            'service_id' => ['required', Rule::exists('services', 'id')->where('business_id', $business->id)],
            'resource_id' => ['nullable', Rule::exists('resources', 'id')->where('business_id', $business->id)],
            'branch_id' => ['nullable', Rule::exists('branches', 'id')->where('business_id', $business->id)],
            'date' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:H:i'],
            'status' => ['required', Rule::in(['scheduled', 'confirmed', 'cancelled', 'completed'])],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);

        $service = Service::where('business_id', $business->id)->findOrFail($attributes['service_id']);
        $startsAt = CarbonImmutable::parse($attributes['date'].' '.$attributes['starts_at'], $business->timezone);
        $endsAt = $startsAt->copy()->addMinutes($service->duration_minutes);

        $errors = $this->availability->validate(
            $business,
            $service,
            $startsAt,
            $endsAt,
            (int) $attributes['professional_id'],
            ($attributes['resource_id'] ?? null) ? (int) $attributes['resource_id'] : null,
            $appointment?->id,
        );

        if ($errors !== []) {
            validator([], [])->after(function (Validator $validator) use ($errors): void {
                foreach ($errors as $error) {
                    $validator->errors()->add('starts_at', $error);
                }
            })->validate();
        }

        return [
            'client_id' => $attributes['client_id'] ?? null,
            'professional_id' => $attributes['professional_id'],
            'service_id' => $attributes['service_id'],
            'resource_id' => $attributes['resource_id'] ?? null,
            'branch_id' => $attributes['branch_id'] ?? null,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'status' => $attributes['status'],
            'notes' => $attributes['notes'] ?? null,
        ];
    }

    private function authorizeTenant(Appointment $appointment): void
    {
        abort_unless($appointment->business_id === app('activeBusiness')->id, 404);
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['professional_id'], fn (Builder $query, int $id) => $query->where('professional_id', $id))
            ->when($filters['service_id'], fn (Builder $query, int $id) => $query->where('service_id', $id))
            ->when($filters['status'], fn (Builder $query, string $status) => $query->where('status', $status));
    }

    private function availabilityWarnings(Request $request, Appointment $appointment): array
    {
        $business = app('activeBusiness');
        $serviceId = $request->old('service_id', $request->input('service_id', $appointment->service_id));
        $professionalId = $request->old('professional_id', $request->input('professional_id', $appointment->professional_id));
        $resourceId = $request->old('resource_id', $request->input('resource_id', $appointment->resource_id));
        $date = $request->old('date', $request->input('date', $appointment->exists ? $appointment->starts_at->format('Y-m-d') : null));
        $time = $request->old('starts_at', $request->input('starts_at', $appointment->exists ? $appointment->starts_at->format('H:i') : null));

        if (! $serviceId || ! $professionalId || ! $date || ! $time) {
            return [];
        }

        $service = Service::where('business_id', $business->id)->find($serviceId);

        if (! $service) {
            return [];
        }

        $startsAt = CarbonImmutable::parse($date.' '.$time, $business->timezone);
        $endsAt = $startsAt->addMinutes($service->duration_minutes);

        return $this->availability->validate(
            $business,
            $service,
            $startsAt,
            $endsAt,
            (int) $professionalId,
            $resourceId ? (int) $resourceId : null,
            $appointment->id,
        );
    }

    private function statuses(): array
    {
        return [
            'scheduled' => 'Programada',
            'confirmed' => 'Confirmada',
            'cancelled' => 'Cancelada',
            'completed' => 'Completada',
        ];
    }
}
