<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function index()
    {
        $business = app('activeBusiness');

        return view('services.index', [
            'business' => $business,
            'services' => $business->services()->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return view('services.form', ['service' => new Service, 'business' => app('activeBusiness')]);
    }

    public function store(Request $request)
    {
        app('activeBusiness')->services()->create($this->validated($request));

        return redirect()->route('servicios.index')->with('status', 'Servicio creado.');
    }

    public function edit(Service $servicio)
    {
        $this->authorizeTenant($servicio);

        return view('services.form', ['service' => $servicio, 'business' => app('activeBusiness')]);
    }

    public function update(Request $request, Service $servicio)
    {
        $this->authorizeTenant($servicio);
        $servicio->update($this->validated($request));

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
        ]);

        return [
            'name' => $attributes['name'],
            'duration_minutes' => $attributes['duration_minutes'],
            'price_cents' => (int) round(($attributes['price'] ?? 0) * 100),
            'description' => $attributes['description'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function authorizeTenant(Service $service): void
    {
        abort_unless($service->business_id === app('activeBusiness')->id, 404);
    }
}
