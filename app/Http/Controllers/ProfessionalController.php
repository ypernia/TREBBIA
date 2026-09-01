<?php

namespace App\Http\Controllers;

use App\Models\Professional;
use Illuminate\Http\Request;

class ProfessionalController extends Controller
{
    public function index()
    {
        $business = app('activeBusiness');

        return view('professionals.index', [
            'business' => $business,
            'professionals' => $business->professionals()->with('branch')->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return view('professionals.form', [
            'professional' => new Professional,
            'business' => app('activeBusiness'),
            'branches' => app('activeBusiness')->branches()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        app('activeBusiness')->professionals()->create($this->validated($request));

        return redirect()->route('profesionales.index')->with('status', 'Profesional creado.');
    }

    public function edit(Professional $profesionale)
    {
        $this->authorizeTenant($profesionale);

        return view('professionals.form', [
            'professional' => $profesionale,
            'business' => app('activeBusiness'),
            'branches' => app('activeBusiness')->branches()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Professional $profesionale)
    {
        $this->authorizeTenant($profesionale);
        $profesionale->update($this->validated($request));

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

        return $request->validate([
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
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function authorizeTenant(Professional $professional): void
    {
        abort_unless($professional->business_id === app('activeBusiness')->id, 404);
    }
}
