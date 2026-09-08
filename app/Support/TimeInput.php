<?php

namespace App\Support;

class TimeInput
{
    public const VALIDATION_RULE = 'date_format:H:i,H:i:s';

    public static function normalize(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }

}
