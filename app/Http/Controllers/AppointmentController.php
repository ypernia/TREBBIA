<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Services\AppointmentAvailabilityService;
use Carbon\CarbonImmutable;
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
        $date = $request->date('date', today());

        return view('appointments.index', [
            'business' => $business,
            'date' => $date,
            'appointments' => $business->appointments()
                ->with(['client', 'professional', 'service', 'resource'])
                ->whereDate('starts_at', $date)
                ->orderBy('starts_at')
                ->get(),
            'upcoming' => $business->appointments()
                ->with(['client', 'professional', 'service'])
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(8)
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('appointments.form', $this->formData(new Appointment));
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

        return view('appointments.form', $this->formData($appointment));
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

    private function formData(Appointment $appointment): array
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
            'statuses' => ['scheduled' => 'Programada', 'confirmed' => 'Confirmada', 'cancelled' => 'Cancelada', 'completed' => 'Completada'],
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
}
