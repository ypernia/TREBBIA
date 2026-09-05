<?php

namespace App\Services;

use App\Models\Plan;
use Illuminate\Support\Collection;

class PlanCatalog
{
    public function sync(): Collection
    {
        $configuredCodes = collect(config('trebbia.plans'))->pluck('code')->all();

        Plan::query()
            ->whereNotIn('code', $configuredCodes)
            ->where(function ($query): void {
                $query->where('monthly_price_cents', 0)
                    ->orWhereIn('code', ['starter', 'free']);
            })
            ->update(['is_active' => false]);

        return collect(config('trebbia.plans'))
            ->map(function (array $plan): Plan {
                return Plan::updateOrCreate(
                    ['code' => $plan['code']],
                    [
                        'name' => $plan['name'],
                        'description' => $plan['description'] ?? null,
                        'currency' => $plan['currency'] ?? 'COP',
                        'monthly_price_cents' => $plan['monthly_price_cents'],
                        'annual_price_cents' => $plan['annual_price_cents'] ?? null,
                        'limits' => $plan['limits'] ?? [],
                        'entitlements' => $plan['entitlements'] ?? [],
                        'features' => $plan['features'] ?? [],
                        'sort_order' => $plan['sort_order'] ?? 0,
                        'is_active' => true,
                    ],
                );
            })
            ->sortBy('sort_order')
            ->values();
    }

    public function active(): Collection
    {
        return Plan::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('monthly_price_cents')
            ->get();
    }
}
