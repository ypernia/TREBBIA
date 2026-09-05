<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Services\PlanEntitlements;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index()
    {
        $business = app('activeBusiness');

        return view('services.index', [
            'business' => $business,
            'services' => $business->services()->withCount('professionals')->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return view('services.form', [
            'service' => new Service,
            'business' => app('activeBusiness'),
            'professionals' => app('activeBusiness')->professionals()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $business = app('activeBusiness');
        abort_unless(app(PlanEntitlements::class)->can($business, 'service.manage'), 403);

        if (! app(PlanEntitlements::class)->hasCapacity($business, 'services')) {
            return back()->withErrors(['name' => 'Alcanzaste el limite de servicios de tu membresia.'])->withInput();
        }

        $attributes = $this->validated($request);
        $service = $business->services()->create($attributes['service']);
        $this->syncProfessionals($service, $attributes['professional_ids']);

        return redirect()->route('servicios.index')->with('status', 'Servicio creado.');
    }

    public function edit(Service $servicio)
    {
        $this->authorizeTenant($servicio);

        return view('services.form', [
            'service' => $servicio,
            'business' => app('activeBusiness'),
            'professionals' => app('activeBusiness')->professionals()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Service $servicio)
    {
        $this->authorizeTenant($servicio);
        $attributes = $this->validated($request);
        $servicio->update($attributes['service']);
        $this->syncProfessionals($servicio, $attributes['professional_ids']);

        return redirect()->route('servicios.index')->with('status', 'Servicio actualizado.');
    }

    public function destroy(Service $servicio)
    {
        $this->authorizeTenant($servicio);
        $servicio->delete();

        return redirect()->route('servicios.index')->with('status', 'Servicio archivado.');
    }

    private function validated(Request $request): array
    {
        $attributes = $request->validate([
            'name' => [
                'required',
                'string',
                'max:140',
                Rule::unique('services')->where('business_id', app('activeBusiness')->id)->ignore($request->route('servicio')),
            ],
            'duration_minutes' => ['required', 'integer', 'min:10', 'max:720'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'description' => ['nullable', 'string', 'max:800'],
            'is_active' => ['nullable', 'boolean'],
            'professional_ids' => ['nullable', 'array'],
            'professional_ids.*' => [Rule::exists('professionals', 'id')->where('business_id', app('activeBusiness')->id)],
        ]);

        return [
            'service' => [
                'name' => $attributes['name'],
                'duration_minutes' => $attributes['duration_minutes'],
                'price_cents' => (int) round(($attributes['price'] ?? 0) * 100),
                'description' => $attributes['description'] ?? null,
                'is_active' => $request->boolean('is_active'),
            ],
            'professional_ids' => collect($attributes['professional_ids'] ?? [])->map(fn ($id) => (int) $id)->all(),
        ];
    }

    private function authorizeTenant(Service $service): void
    {
        abort_unless($service->business_id === app('activeBusiness')->id, 404);
    }

    private function syncProfessionals(Service $service, array $professionalIds): void
    {
        $service->professionals()->syncWithPivotValues($professionalIds, ['business_id' => app('activeBusiness')->id]);
    }
}
