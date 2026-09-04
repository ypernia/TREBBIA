<?php

namespace App\Services;

use App\Contracts\BookingIntentInterpreter;
use App\Models\Business;
use Carbon\CarbonImmutable;
use Illuminate\Support\Str;

class RuleBasedBookingIntentInterpreter implements BookingIntentInterpreter
{
    public function __construct(private BookingEngine $booking) {}

    public function interpret(Business $business, string $message, array $context = []): array
    {
        $normalized = $this->normalize($message);

        return array_filter([
            'intent' => $this->intent($normalized, $context),
            'service_id' => $this->serviceId($business, $normalized),
            'professional_id' => $this->professionalId($business, $normalized, $context),
            'date' => $this->date($business, $message, $normalized)?->toDateString(),
            'time' => $this->time($normalized),
            'time_after' => $this->timeAfter($normalized),
            'reset' => Str::contains($normalized, ['cancelar', 'reiniciar']),
        ], fn ($value) => $value !== null && $value !== false && $value !== '');
    }

    private function intent(string $message, array $context): ?string
    {
        if (($context['intent'] ?? null) === 'booking') {
            return 'booking';
        }

        return Str::contains($message, ['agendar', 'reservar', 'cita', 'turno', 'consulta'])
            ? 'booking'
            : null;
    }

    private function serviceId(Business $business, string $message): ?int
    {
        $service = $this->booking->services($business)->first(function ($service) use ($message): bool {
            $name = $this->normalize($service->name);

            return str_contains($message, $name)
                || str_contains($name, $message)
                || collect(explode(' ', $name))
                    ->filter(fn (string $word): bool => strlen($word) >= 4)
                    ->contains(fn (string $word): bool => str_contains($message, $word));
        });

        return $service?->id;
    }

    private function professionalId(Business $business, string $message, array $context): ?int
    {
        $service = $this->booking->service($business, $context['service_id'] ?? null);
        $professionals = $service
            ? $this->booking->professionalsForService($business, $service)
            : $business->professionals()->where('is_active', true)->orderBy('name')->get();

        $professional = $professionals->first(function ($professional) use ($message): bool {
            $name = $this->normalize($professional->name);

            return str_contains($message, $name)
                || collect(explode(' ', $name))
                    ->filter(fn (string $word): bool => strlen($word) >= 3)
                    ->contains(fn (string $word): bool => str_contains($message, $word));
        });

        return $professional?->id;
    }

    private function date(Business $business, string $original, string $message): ?CarbonImmutable
    {
        $today = CarbonImmutable::now($business->timezone)->startOfDay();

        if (str_contains($message, 'pasado manana')) {
            return $today->addDays(2);
        }

        if (str_contains($message, 'manana')) {
            return $today->addDay();
        }

        if (preg_match('/\b(hoy)\b/', $message)) {
            return $today;
        }

        if (preg_match('/\b(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})\b/', $original, $match)) {
            return CarbonImmutable::create((int) $match[1], (int) $match[2], (int) $match[3], 0, 0, 0, $business->timezone);
        }

        if (preg_match('/\b(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})\b/', $original, $match)) {
            return CarbonImmutable::create((int) $match[3], (int) $match[2], (int) $match[1], 0, 0, 0, $business->timezone);
        }

        if (preg_match('/\b(\d{1,2})\s+de\s+([a-z]+)(?:\s+de\s+(\d{4}))?\b/', $message, $match)) {
            $month = $this->monthNumber($match[2]);

            if ($month) {
                $year = isset($match[3]) ? (int) $match[3] : (int) $today->format('Y');
                $date = CarbonImmutable::create($year, $month, (int) $match[1], 0, 0, 0, $business->timezone);

                return $date->lessThan($today) && ! isset($match[3]) ? $date->addYear() : $date;
            }
        }

        foreach ($this->weekdays() as $name => $weekday) {
            if (preg_match("/\b{$name}\b/", $message)) {
                return $today->next($weekday);
            }
        }

        return null;
    }

    private function time(string $message): ?string
    {
        if (str_contains($message, 'despues de')) {
            return null;
        }

        if (preg_match('/\b(?:a\s+las\s+)?([01]?\d|2[0-3]):([0-5]\d)\b/', $message, $match)) {
            return str_pad($match[1], 2, '0', STR_PAD_LEFT).':'.$match[2];
        }

        if (preg_match('/\b(?:a\s+las\s+)?([1-9]|1[0-2])\s*(a\.?\s*m\.?|p\.?\s*m\.?|am|pm)\b/', $message, $match)) {
            return $this->clockTime((int) $match[1], str_contains(str_replace('.', '', $match[2]), 'p'));
        }

        if (preg_match('/\ba\s+las\s+([1-9]|1[0-2])\b/', $message, $match)) {
            return $this->clockTime((int) $match[1], (int) $match[1] < 7);
        }

        return null;
    }

    private function timeAfter(string $message): ?string
    {
        if (! preg_match('/\bdespues\s+de\s+(?:las\s+)?([1-9]|1[0-2]|2[0-3])(?::([0-5]\d))?\s*(a\.?\s*m\.?|p\.?\s*m\.?|am|pm)?\b/', $message, $match)) {
            return null;
        }

        $hour = (int) $match[1];
        $isPm = isset($match[3])
            ? str_contains(str_replace('.', '', $match[3]), 'p')
            : $hour < 7;

        return $this->clockTime($hour, $isPm, $match[2] ?? '00');
    }

    private function clockTime(int $hour, bool $isPm = false, string $minutes = '00'): string
    {
        if ($isPm && $hour < 12) {
            $hour += 12;
        }

        if (! $isPm && $hour === 12) {
            $hour = 0;
        }

        return str_pad((string) $hour, 2, '0', STR_PAD_LEFT).':'.$minutes;
    }

    private function monthNumber(string $month): ?int
    {
        return [
            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'setiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12,
        ][$month] ?? null;
    }

    private function weekdays(): array
    {
        return [
            'lunes' => 1,
            'martes' => 2,
            'miercoles' => 3,
            'jueves' => 4,
            'viernes' => 5,
            'sabado' => 6,
            'domingo' => 7,
        ];
    }

    private function normalize(string $value): string
    {
        return Str::lower(Str::ascii(trim($value)));
    }
}
