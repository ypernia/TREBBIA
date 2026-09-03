<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function index(): View
    {
        $business = app('activeBusiness');
        $plans = $this->ensurePlans();
        $subscription = $this->ensureSubscription($plans->firstWhere('code', 'starter'));
        $currentPlan = $subscription->plan;
        $usage = $this->usage();

        return view('membership.index', [
            'business' => $business,
            'plans' => $plans,
            'subscription' => $subscription,
            'currentPlan' => $currentPlan,
            'usage' => $usage,
            'limits' => $this->limits($currentPlan, $usage),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $plans = $this->ensurePlans();
        $attributes = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'status' => ['required', Rule::in(array_keys($this->statusLabels()))],
        ]);

        abort_unless($plans->contains('id', (int) $attributes['plan_id']), 404);

        $this->ensureSubscription($plans->first())->update([
            'plan_id' => $attributes['plan_id'],
            'status' => $attributes['status'],
            'current_period_ends_at' => now()->addMonth(),
        ]);

        return redirect()->route('membership.index')->with('status', 'Membresia actualizada.');
    }

    private function ensurePlans()
    {
        return collect(config('trebbia.plans'))
            ->map(function (array $plan): Plan {
                return Plan::updateOrCreate(
                    ['code' => $plan['code']],
                    [
                        'name' => $plan['name'],
                        'monthly_price_cents' => $plan['monthly_price_cents'],
                        'limits' => $plan['limits'],
                        'features' => $plan['features'],
                        'is_active' => true,
                    ],
                );
            })
            ->sortBy('monthly_price_cents')
            ->values();
    }

    private function ensureSubscription(?Plan $defaultPlan)
    {
        return app('activeBusiness')->subscription()->firstOrCreate(
            [],
            [
                'plan_id' => $defaultPlan?->id,
                'status' => 'trialing',
                'trial_ends_at' => now()->addDays(14),
                'current_period_ends_at' => now()->addMonth(),
            ],
        )->load('plan');
    }

    private function usage(): array
    {
        $business = app('activeBusiness');
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        return [
            'monthly_appointments' => $business->appointments()->whereBetween('starts_at', [$monthStart, $monthEnd])->count(),
            'professionals' => $business->professionals()->where('is_active', true)->count(),
            'services' => $business->services()->where('is_active', true)->count(),
            'branches' => $business->branches()->where('is_active', true)->count(),
            'users' => $business->businessUsers()->where('is_active', true)->count(),
            'public_booking' => (bool) ($business->settings()->first()?->public_booking_settings['allow_public_booking'] ?? false),
            'automations' => $business->appointmentReminders()->exists(),
        ];
    }

    private function limits(?Plan $plan, array $usage): array
    {
        $limits = $plan?->limits ?? [];
        $labels = [
            'monthly_appointments' => 'Citas mensuales',
            'professionals' => 'Profesionales',
            'services' => 'Servicios',
            'branches' => 'Sedes',
            'users' => 'Usuarios',
            'public_booking' => 'Reservas publicas',
            'automations' => 'Automatizaciones',
        ];

        return collect($labels)
            ->map(fn (string $label, string $key): array => [
                'key' => $key,
                'label' => $label,
                'used' => $usage[$key],
                'limit' => $limits[$key] ?? null,
                'percent' => $this->percent($usage[$key], $limits[$key] ?? null),
                'is_enabled' => is_bool($limits[$key] ?? null) ? (bool) $limits[$key] : true,
            ])
            ->values()
            ->all();
    }

    private function percent(mixed $used, mixed $limit): int
    {
        if (is_bool($limit)) {
            return $used ? 100 : 0;
        }

        if (! $limit) {
            return 0;
        }

        return min(100, (int) round(((int) $used / (int) $limit) * 100));
    }

    private function statusLabels(): array
    {
        return [
            'trialing' => 'Prueba',
            'active' => 'Activa',
            'past_due' => 'Pago pendiente',
            'cancelled' => 'Cancelada',
        ];
    }
}
