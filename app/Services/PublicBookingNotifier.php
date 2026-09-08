<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BookingRequest;
use App\Models\Business;

class PublicBookingNotifier
{
    public function __construct(private AppointmentNotificationService $notifications) {}

    public function notify(Business $business, BookingRequest|Appointment $reservation, bool $requiresManualConfirmation): void
    {
        if ($reservation instanceof BookingRequest) {
            $this->notifications->bookingRequested($reservation);

            return;
        }

        $this->notifications->publicAppointmentConfirmed($reservation);
    }
}
