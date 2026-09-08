<?php

namespace App\Http\Controllers;

use App\Models\BusinessSchedule;
use App\Models\Professional;
use App\Models\Service;
use App\Support\BusinessIndustries;
use App\Support\TimeInput;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    private array $steps = ['negocio', 'horarios', 'servicio', 'profesional', 'finalizar'];

    public function show(?string $step = null)
    {
        $step = $this->normalizeStep($step);
        $business = app('activeBusiness');

        return view("onboarding.{$step}", [
            'business' => $business,
            'steps' => $this->steps,
            'step' => $step,
        ]);
    }

    public function store(Request $request, string $step)
    {
        $business = app('activeBusiness');
        $step = $this->normalizeStep($step);

        if ($step === 'negocio') {
            $attributes = $request->validate([
                'name' => ['required', 'string', 'max:160'],
                ...BusinessIndustries::validationRules(),
                'email' => ['nullable', 'email', 'max:180'],
                'phone' => ['nullable', 'string', 'max:60'],
            ]);
            unset($attributes['industry_other']);
            $attributes['industry'] = BusinessIndustries::resolveFromRequest($request);

            $business->update($attributes);

            return redirect()->route('onboarding.show', ['step' => 'horarios']);
        }

        if ($step === 'horarios') {
            $attributes = $request->validate([
                'opens_at' => ['required', TimeInput::VALIDATION_RULE],
                'closes_at' => ['required', TimeInput::VALIDATION_RULE, 'after:opens_at'],
                'weekdays' => ['array'],
                'weekdays.*' => ['integer', 'between:1,7'],
            ], [
                'opens_at.required' => 'Indica la hora de apertura.',
                'opens_at.date_format' => 'La hora de apertura debe ser valida, por ejemplo 08:00.',
                'closes_at.required' => 'Indica la hora de cierre.',
                'closes_at.date_format' => 'La hora de cierre debe ser valida, por ejemplo 18:00.',
                'closes_at.after' => 'La hora de cierre debe ser posterior a la hora de apertura.',
            ], [
                'opens_at' => 'hora de apertura',
                'closes_at' => 'hora de cierre',
                'weekdays' => 'dias laborales',
            ]);

            $selected = collect($attributes['weekdays'] ?? []);

            foreach (range(1, 7) as $weekday) {
                BusinessSchedule::updateOrCreate(
                    ['business_id' => $business->id, 'branch_id' => null, 'weekday' => $weekday],
                    [
                        'opens_at' => TimeInput::normalize($attributes['opens_at']),
                        'closes_at' => TimeInput::normalize($attributes['closes_at']),
                        'is_closed' => ! $selected->contains($weekday),
                    ],
                );
            }

            return redirect()->route('onboarding.show', ['step' => 'servicio']);
        }

        if ($step === 'servicio') {
            $attributes = $request->validate([
                'name' => [
                    'required',
                    'string',
                    'max:140',
                    Rule::unique('services')->where('business_id', $business->id),
                ],
                'duration_minutes' => ['required', 'integer', 'min:10', 'max:720'],
                'price' => ['nullable', 'numeric', 'min:0'],
                'description' => ['nullable', 'string', 'max:800'],
            ]);

            Service::create([
                'business_id' => $business->id,
                'name' => $attributes['name'],
                'duration_minutes' => $attributes['duration_minutes'],
                'price_cents' => (int) round(($attributes['price'] ?? 0) * 100),
                'description' => $attributes['description'] ?? null,
            ]);

            return redirect()->route('onboarding.show', ['step' => 'profesional']);
        }

        if ($step === 'profesional') {
            $attributes = $request->validate([
                'name' => ['required', 'string', 'max:140'],
                'title' => ['nullable', 'string', 'max:120'],
                'email' => ['nullable', 'email', 'max:180'],
                'phone' => ['nullable', 'string', 'max:60'],
            ]);

            Professional::create([
                ...$attributes,
                'business_id' => $business->id,
                'branch_id' => $business->branches()->where('is_main', true)->value('id'),
            ]);

            return redirect()->route('onboarding.show', ['step' => 'finalizar']);
        }

        $business->update([
            'status' => 'active',
            'onboarding_completed_at' => now(),
        ]);

        return redirect()->route('dashboard')->with('status', 'Configuracion inicial completada.');
    }

    private function normalizeStep(?string $step): string
    {
        return in_array($step, $this->steps, true) ? $step : 'negocio';
    }
}
