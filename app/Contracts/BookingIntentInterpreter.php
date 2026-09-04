<?php

namespace App\Contracts;

use App\Models\Business;

interface BookingIntentInterpreter
{
    public function interpret(Business $business, string $message, array $context = []): array;
}
