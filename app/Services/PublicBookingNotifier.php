<?php

namespace App\Services;

use App\Mail\PublicBookingReceived;
use App\Models\Appointment;
use App\Models\BookingRequest;
use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PublicBookingNotifier
{
    public function notify(Business $business, BookingRequest|Appointment $reservation, bool $requiresManualConfirmation): void
    {
        $settings = $business->settings()->firstOrCreate([]);
        $preferences = $settings->notification_preferences ?? [];

        if (! (bool) ($preferences['email'] ?? false)) {
            return;
        }

        $reservation->loadMissing(['client', 'professional', 'service']);
        $recipients = $this->recipients($business, $reservation);

        if ($recipients === []) {
            return;
        }

        try {
            Mail::to($recipients)->send(new PublicBookingReceived($business, $reservation, $requiresManualConfirmation));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function recipients(Business $business, BookingRequest|Appointment $reservation): array
    {
        $emails = collect([
            $business->email,
            $business->owner?->email,
            $reservation->professional?->email,
        ]);

        $business->users()
            ->wherePivot('is_active', true)
            ->wherePivotIn('role', [
                BusinessUser::ROLE_OWNER,
                BusinessUser::ROLE_ADMIN,
                BusinessUser::ROLE_RECEPTIONIST,
            ])
            ->pluck('email')
            ->each(fn (?string $email) => $emails->push($email));

        return $emails
            ->filter(fn (?string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false)
            ->map(fn (string $email): string => mb_strtolower($email))
            ->unique()
            ->values()
            ->all();
    }
}
