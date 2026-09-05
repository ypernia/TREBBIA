<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformAuditLog;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\PlanCatalog;
use App\Services\SubscriptionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminSubscriptionController extends Controller
{
    public function index(PlanCatalog $planCatalog): View
    {
        return view('admin.subscriptions.index', [
            'plans' => $planCatalog->sync(),
            'statusLabels' => $this->statusLabels(),
            'subscriptions' => Subscription::with(['business.owner', 'plan'])
                ->latest()
                ->paginate(15),
        ]);
    }

    public function update(Request $request, Subscription $subscription, SubscriptionManager $manager): RedirectResponse
    {
        $attributes = $request->validate([
            'status' => ['required', Rule::in(array_keys($this->statusLabels()))],
            'plan_id' => ['nullable', Rule::exists('plans', 'id')->where('is_active', true)],
            'reason' => ['required', 'string', 'max:180'],
            'redirect_to' => ['nullable', 'url'],
        ]);

        $plan = ($attributes['plan_id'] ?? null)
            ? Plan::where('is_active', true)->findOrFail($attributes['plan_id'])
            : $subscription->plan;

        if ($attributes['status'] === Subscription::STATUS_ACTIVE) {
            abort_unless($plan && $plan->monthly_price_cents > 0, 422, 'Para activar una suscripcion debes seleccionar un plan pago.');
            $manager->activate($subscription, $plan);
        } else {
            $manager->transition($subscription, $attributes['status'], $plan, 'platform_admin_update');
        }

        PlatformAuditLog::record(
            $request,
            'subscription.updated',
            $subscription,
            [
                'status' => $attributes['status'],
                'plan_id' => $plan?->id,
            ],
            $subscription->business_id,
            $attributes['reason'],
        );

        if (! empty($attributes['redirect_to']) && str_starts_with($attributes['redirect_to'], url('/admin'))) {
            return redirect()->to($attributes['redirect_to'])->with('status', 'Suscripcion actualizada.');
        }

        return redirect()->route('admin.subscriptions.index')->with('status', 'Suscripcion actualizada.');
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
