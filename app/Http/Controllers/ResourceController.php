<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ResourceController extends Controller
{
    public function index()
    {
        $business = app('activeBusiness');

        return view('resources.index', [
            'business' => $business,
            'resources' => $business->resources()->with('branch')->latest()->paginate(10),
        ]);
    }

    public function create()
    {
        return view('resources.form', [
            'resource' => new Resource,
            'business' => app('activeBusiness'),
            'branches' => app('activeBusiness')->branches()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        app('activeBusiness')->resources()->create($this->validated($request));

        return redirect()->route('recursos.index')->with('status', 'Recurso creado.');
    }

    public function edit(Resource $recurso)
    {
        $this->authorizeTenant($recurso);

        return view('resources.form', [
            'resource' => $recurso,
            'business' => app('activeBusiness'),
            'branches' => app('activeBusiness')->branches()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Resource $recurso)
    {
        $this->authorizeTenant($recurso);
        $recurso->update($this->validated($request));

        return redirect()->route('recursos.index')->with('status', 'Recurso actualizado.');
    }

    public function destroy(Resource $recurso)
    {
        $this->authorizeTenant($recurso);
        $recurso->delete();

        return redirect()->route('recursos.index')->with('status', 'Recurso archivado.');
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
            'name' => [
                'required',
                'string',
                'max:140',
                Rule::unique('resources')->where('business_id', $business->id)->ignore($request->route('recurso')),
            ],
            'type' => ['nullable', 'string', 'max:120'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function authorizeTenant(Resource $resource): void
    {
        abort_unless($resource->business_id === app('activeBusiness')->id, 404);
    }
}
