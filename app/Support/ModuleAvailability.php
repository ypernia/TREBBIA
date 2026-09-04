<?php

namespace App\Support;

use App\Models\Business;

class ModuleAvailability
{
    public static function isAvailable(string $moduleKey, ?Business $business): bool
    {
        $module = config("trebbia.modules.{$moduleKey}");

        if (! $module || ! $business) {
            return false;
        }

        $industries = $module['available_for_industries'] ?? null;

        if (! $industries) {
            return true;
        }

        $businessIndustry = str($business->industry ?: '')->lower()->ascii()->toString();

        return collect($industries)->contains(
            fn (string $industry): bool => str_contains($businessIndustry, str($industry)->lower()->ascii()->toString())
        );
    }

    public static function clinicalHistory(?Business $business): bool
    {
        return self::isAvailable('historia_clinica', $business);
    }
}
