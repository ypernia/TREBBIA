<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use App\Services\PlanEntitlements;
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
            'branches' => $business->branches()->where('is_active', true)->orderBy('name')->get(),
            'suggestedResources' => $this->suggestedResources(),
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
        $business = app('activeBusiness');
        abort_unless(app(PlanEntitlements::class)->can($business, 'resource.manage'), 403);

        if (! app(PlanEntitlements::class)->hasCapacity($business, 'resources')) {
            return back()->withErrors(['name' => 'Alcanzaste el limite de recursos de tu membresia.'])->withInput();
        }

        $business->resources()->create($this->validated($request));

        return redirect()->route('recursos.index')->with('status', 'Recurso creado.');
    }

    public function storeSuggestions(Request $request)
    {
        $business = app('activeBusiness');
        abort_unless(app(PlanEntitlements::class)->can($business, 'resource.manage'), 403);

        $attributes = $request->validate([
            'resources' => ['required', 'array', 'min:1'],
            'resources.*.name' => ['required', 'string', 'max:140'],
            'resources.*.type' => ['nullable', 'string', 'max:120'],
            'resources.*.selected' => ['nullable', 'boolean'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id', function ($attribute, $value, $fail) use ($business): void {
                if ($value && ! $business->branches()->whereKey($value)->exists()) {
                    $fail('La sede seleccionada no pertenece a este negocio.');
                }
            }],
        ]);

        $created = 0;
        foreach ($attributes['resources'] as $resource) {
            if (! app(PlanEntitlements::class)->hasCapacity($business, 'resources')) {
                break;
            }

            if (! (bool) ($resource['selected'] ?? false)) {
                continue;
            }

            $exists = $business->resources()->where('name', $resource['name'])->exists();
            if ($exists) {
                continue;
            }

            $business->resources()->create([
                'branch_id' => $attributes['branch_id'] ?? null,
                'name' => $resource['name'],
                'type' => $resource['type'] ?? null,
                'is_active' => true,
            ]);
            $created++;
        }

        return redirect()->route('recursos.index')->with('status', "{$created} recurso(s) creados.");
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

    private function suggestedResources(): array
    {
        $business = app('activeBusiness');
        $industry = str($business->industry ?: '')->lower()->ascii()->toString();
        $presets = config('trebbia.resource_presets');
        $key = collect(array_keys($presets))
            ->first(fn (string $presetKey): bool => $presetKey !== 'default' && str_contains($industry, $presetKey));

        return $presets[$key] ?? $presets['default'];
    }
}
