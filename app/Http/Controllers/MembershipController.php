<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PlanCatalog;
use App\Services\PlanEntitlements;
use App\Services\SubscriptionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MembershipController extends Controller
{
    public function __construct(
        private PlanCatalog $plans,
        private PlanEntitlements $entitlements,
        private SubscriptionManager $subscriptions,
    ) {}

    public function index(): View
    {
        $business = app('activeBusiness');
        $plans = $this->plans->sync();
        $subscription = $this->subscriptions->ensure($business);
        $currentPlan = $subscription->plan;
        $usage = $this->entitlements->usage($business);

        return view('membership.index', [
            'business' => $business,
            'plans' => $plans,
            'subscription' => $subscription,
            'currentPlan' => $currentPlan,
            'usage' => $usage,
            'limits' => $this->limits($subscription, $usage),
            'planLimits' => fn (Plan $plan): array => $this->limitsForPlan($plan, $usage),
            'trialDays' => (int) config('trebbia.trial.days', 14),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $plans = $this->plans->sync();
        $attributes = $request->validate([
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
        ]);

        abort_unless($plans->contains('id', (int) $attributes['plan_id']), 404);

        $subscription = $this->subscriptions->ensure(app('activeBusiness'));
        $this->subscriptions->requestPlan($subscription, $plans->firstWhere('id', (int) $attributes['plan_id']));

        return redirect()->route('membership.index')->with('status', 'Membresia solicitada. La activacion queda pendiente de pago o validacion administrativa.');
    }

    private function limits(Subscription $subscription, array $usage): array
    {
        $limits = $this->entitlements->limits($subscription);
        $capabilities = $this->entitlements->entitlements($subscription);

        return $this->formatLimits($limits, $capabilities, $usage);
    }

    private function limitsForPlan(Plan $plan, array $usage): array
    {
        return $this->formatLimits($plan->limits ?? [], $plan->entitlements ?? [], $usage);
    }

    private function formatLimits(array $limits, array $capabilities, array $usage): array
    {
        $labels = [
            'monthly_appointments' => 'Citas mensuales',
            'professionals' => 'Profesionales',
            'services' => 'Servicios',
            'branches' => 'Sedes',
            'users' => 'Usuarios',
            'resources' => 'Recursos',
            'public_booking.enabled' => 'Reservas publicas',
            'whatsapp_link.enabled' => 'Enlace WhatsApp',
            'whatsapp_auto.enabled' => 'WhatsApp automatico',
            'automation.enabled' => 'Automatizaciones',
            'reports.advanced' => 'Reportes avanzados',
        ];

        return collect($labels)
            ->map(fn (string $label, string $key): array => [
                'key' => $key,
                'label' => $label,
                'used' => $usage[$key] ?? null,
                'limit' => $limits[$key] ?? null,
                'percent' => $this->percent($usage[$key] ?? 0, $limits[$key] ?? null),
                'is_enabled' => str_contains($key, '.') ? (bool) ($capabilities[$key] ?? false) : true,
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
            Subscription::STATUS_TRIALING => 'Prueba',
            Subscription::STATUS_ACTIVE => 'Activa',
            Subscription::STATUS_PAST_DUE => 'Pago pendiente',
            Subscription::STATUS_EXPIRED => 'Expirada',
            Subscription::STATUS_CANCELLED => 'Cancelada',
            Subscription::STATUS_SUSPENDED => 'Suspendida',
        ];
    }
}
