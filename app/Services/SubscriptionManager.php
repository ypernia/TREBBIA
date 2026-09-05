<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Plan;
use App\Models\Subscription;

class SubscriptionManager
{
    public function __construct(private PlanCatalog $plans) {}

    public function ensure(Business $business): Subscription
    {
        $this->plans->sync();

        $subscription = $business->subscription()->first();

        if (! $subscription) {
            return $business->subscription()->create([
                'plan_id' => null,
                'status' => Subscription::STATUS_TRIALING,
                'trial_started_at' => now(),
                'trial_ends_at' => now()->addDays((int) config('trebbia.trial.days', 14)),
            ]);
        }

        if ($subscription->status === Subscription::STATUS_TRIALING && $subscription->trial_ends_at?->isPast()) {
            $requestedPlan = $subscription->plan?->monthly_price_cents > 0 ? $subscription->plan : null;
            $this->transition($subscription, Subscription::STATUS_EXPIRED, $requestedPlan, 'trial_expired');
        }

        if ($subscription->plan?->monthly_price_cents === 0 && $subscription->status !== Subscription::STATUS_TRIALING) {
            $this->transition($subscription, Subscription::STATUS_EXPIRED, null, 'legacy_free_removed');
        }

        if ($subscription->status === Subscription::STATUS_TRIALING && $subscription->plan?->monthly_price_cents === 0) {
            $subscription->update(['plan_id' => null]);
        }

        return $subscription->refresh()->load('plan');
    }

    public function activate(Subscription $subscription, Plan $plan): Subscription
    {
        abort_if($plan->monthly_price_cents <= 0 || ! $plan->is_active, 422, 'Selecciona una membresia paga activa.');

        return $this->transition($subscription, Subscription::STATUS_ACTIVE, $plan, 'manual_activation');
    }

    public function requestPlan(Subscription $subscription, Plan $plan): Subscription
    {
        abort_if($plan->monthly_price_cents <= 0 || ! $plan->is_active, 422, 'Selecciona una membresia paga activa.');

        $fromStatus = $subscription->status;
        $fromPlanId = $subscription->plan_id;
        $nextStatus = $subscription->isTrialing() ? Subscription::STATUS_TRIALING : Subscription::STATUS_PAST_DUE;

        $subscription->update([
            'plan_id' => $plan->id,
            'status' => $nextStatus,
            'current_period_started_at' => null,
            'current_period_ends_at' => null,
        ]);

        $subscription->business->subscriptionEvents()->create([
            'subscription_id' => $subscription->id,
            'from_status' => $fromStatus,
            'to_status' => $nextStatus,
            'from_plan_id' => $fromPlanId,
            'to_plan_id' => $plan->id,
            'reason' => 'plan_requested',
        ]);

        return $subscription->refresh()->load('plan');
    }

    public function transition(Subscription $subscription, string $status, ?Plan $plan, string $reason): Subscription
    {
        $fromStatus = $subscription->status;
        $fromPlanId = $subscription->plan_id;

        $subscription->update([
            'plan_id' => $plan?->id,
            'status' => $status,
            'current_period_started_at' => $status === Subscription::STATUS_ACTIVE ? now() : $subscription->current_period_started_at,
            'current_period_ends_at' => $status === Subscription::STATUS_ACTIVE ? now()->addMonth() : $subscription->current_period_ends_at,
            'cancelled_at' => $status === Subscription::STATUS_CANCELLED ? now() : $subscription->cancelled_at,
            'ended_at' => in_array($status, [Subscription::STATUS_EXPIRED, Subscription::STATUS_CANCELLED, Subscription::STATUS_SUSPENDED], true) ? now() : null,
        ]);

        $subscription->business->subscriptionEvents()->create([
            'subscription_id' => $subscription->id,
            'from_status' => $fromStatus,
            'to_status' => $status,
            'from_plan_id' => $fromPlanId,
            'to_plan_id' => $plan?->id,
            'reason' => $reason,
        ]);

        return $subscription->refresh()->load('plan');
    }
}
