<?php

namespace App\Services;

use App\Mail\AppointmentTransactionalNotification;
use App\Models\Appointment;
use App\Models\BookingRequest;
use App\Models\Business;
use App\Models\BusinessUser;
use Illuminate\Support\Facades\Mail;
use Throwable;

class AppointmentNotificationService
{
    public function bookingRequested(BookingRequest $bookingRequest): void
    {
        $bookingRequest->loadMissing(['business.owner', 'client', 'professional', 'service']);

        $this->sendToBusiness(
            $bookingRequest->business,
            $bookingRequest,
            'booking_requested',
        );
    }

    public function appointmentConfirmed(Appointment $appointment): void
    {
        $appointment->loadMissing(['business', 'client', 'professional', 'service']);

        $this->sendToClient($appointment, 'appointment_confirmed');
    }

    public function publicAppointmentConfirmed(Appointment $appointment): void
    {
        $appointment->loadMissing(['business.owner', 'client', 'professional', 'service']);

        $this->sendToBusiness($appointment->business, $appointment, 'public_appointment_confirmed');
        $this->sendToClient($appointment, 'appointment_confirmed');
    }

    public function bookingRejected(BookingRequest $bookingRequest): void
    {
        $bookingRequest->loadMissing(['business', 'client', 'professional', 'service']);

        $this->sendToClient($bookingRequest, 'booking_rejected');
    }

    public function appointmentCancelled(Appointment $appointment): void
    {
        $appointment->loadMissing(['business', 'client', 'professional', 'service']);

        $this->sendToClient($appointment, 'appointment_cancelled');
    }

    public function appointmentRescheduled(Appointment $appointment, array $previousSchedule): void
    {
        $appointment->loadMissing(['business', 'client', 'professional', 'service']);

        $this->sendToClient($appointment, 'appointment_rescheduled', $previousSchedule);
    }

    private function sendToBusiness(Business $business, Appointment|BookingRequest $reservation, string $event): void
    {
        if (! $this->emailEnabled($business)) {
            return;
        }

        $recipients = $this->businessRecipients($business, $reservation);

        if ($recipients === []) {
            return;
        }

        $this->send($business, $reservation, $event, 'business', $recipients);
    }

    private function sendToClient(Appointment|BookingRequest $reservation, string $event, ?array $previousSchedule = null): void
    {
        $business = $reservation->business;

        if (! $business || ! $this->emailEnabled($business)) {
            return;
        }

        $email = $reservation->client?->email;

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $this->send($business, $reservation, $event, 'client', [mb_strtolower($email)], $previousSchedule);
    }

    private function send(
        Business $business,
        Appointment|BookingRequest $reservation,
        string $event,
        string $recipientType,
        array $recipients,
        ?array $previousSchedule = null,
    ): void {
        try {
            Mail::to($recipients)->send(new AppointmentTransactionalNotification(
                $business,
                $reservation,
                $event,
                $recipientType,
                $previousSchedule,
            ));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function emailEnabled(Business $business): bool
    {
        $settings = $business->settings()->firstOrCreate([]);
        $preferences = $settings->notification_preferences ?? [];

        return (bool) ($preferences['email'] ?? false);
    }

    private function businessRecipients(Business $business, Appointment|BookingRequest $reservation): array
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
