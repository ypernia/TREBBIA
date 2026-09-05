<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Subscription;

class PlanEntitlements
{
    public function can(Business $business, string $capability): bool
    {
        $subscription = app(SubscriptionManager::class)->ensure($business);

        if (! $subscription->hasOperationalAccess()) {
            return false;
        }

        return (bool) ($this->entitlements($subscription)[$capability] ?? false);
    }

    public function limit(Business $business, string $key): mixed
    {
        $subscription = app(SubscriptionManager::class)->ensure($business);

        return $this->limits($subscription)[$key] ?? null;
    }

    public function entitlements(Subscription $subscription): array
    {
        if ($subscription->isTrialing()) {
            return config('trebbia.trial.entitlements', []);
        }

        if ($subscription->status !== Subscription::STATUS_ACTIVE) {
            return [];
        }

        return $subscription->plan?->entitlements ?? [];
    }

    public function limits(Subscription $subscription): array
    {
        if ($subscription->isTrialing()) {
            return config('trebbia.trial.limits', []);
        }

        if ($subscription->status !== Subscription::STATUS_ACTIVE) {
            return [];
        }

        return $subscription->plan?->limits ?? [];
    }

    public function usage(Business $business): array
    {
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        return [
            'monthly_appointments' => $business->appointments()->whereBetween('starts_at', [$monthStart, $monthEnd])->count(),
            'professionals' => $business->professionals()->where('is_active', true)->count(),
            'services' => $business->services()->where('is_active', true)->count(),
            'branches' => $business->branches()->where('is_active', true)->count(),
            'users' => $business->businessUsers()->where('is_active', true)->count(),
            'resources' => $business->resources()->where('is_active', true)->count(),
            'public_booking' => (bool) ($business->settings()->first()?->public_booking_settings['allow_public_booking'] ?? false),
            'automations' => $business->appointmentReminders()->exists(),
        ];
    }

    public function hasCapacity(Business $business, string $key): bool
    {
        $limit = $this->limit($business, $key);

        if ($limit === null) {
            return true;
        }

        return ($this->usage($business)[$key] ?? 0) < (int) $limit;
    }
}
