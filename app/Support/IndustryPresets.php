<?php

namespace App\Support;

use App\Models\Business;

class IndustryPresets
{
    public static function keyFor(Business $business): string
    {
        $industry = str($business->industry ?: '')->lower()->ascii()->toString();
        $keys = collect(array_keys(config('trebbia.service_presets', [])))
            ->merge(array_keys(config('trebbia.resource_presets', [])))
            ->unique()
            ->reject(fn (string $key): bool => $key === 'default');

        return $keys->first(fn (string $key): bool => str_contains($industry, $key)) ?: 'default';
    }

    public static function servicesFor(Business $business): array
    {
        $presets = config('trebbia.service_presets', []);

        return $presets[self::keyFor($business)] ?? $presets['default'] ?? [];
    }

    public static function resourcesFor(Business $business): array
    {
        $presets = config('trebbia.resource_presets', []);

        return $presets[self::keyFor($business)] ?? $presets['default'] ?? [];
    }

    public static function modulesFor(Business $business): array
    {
        $presets = config('trebbia.industry_modules', []);

        return $presets[self::keyFor($business)] ?? $presets['default'] ?? [];
    }
}
