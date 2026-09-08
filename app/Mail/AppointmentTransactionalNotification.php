<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\BookingRequest;
use App\Models\Business;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class AppointmentTransactionalNotification extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Business $business,
        public Appointment|BookingRequest $reservation,
        public string $event,
        public string $recipientType,
        public ?array $previousSchedule = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.booking_from.address'),
                config('mail.booking_from.name'),
            ),
            subject: $this->notificationSubject(),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.appointment-transactional-notification');
    }

    private function notificationSubject(): string
    {
        return match ($this->event) {
            'booking_requested' => 'Nueva solicitud de reserva en TREBBIA',
            'public_appointment_confirmed' => 'Nueva cita confirmada en TREBBIA',
            'appointment_confirmed' => 'Tu cita fue confirmada',
            'booking_rejected' => 'Tu solicitud de reserva no fue aprobada',
            'appointment_cancelled' => 'Tu cita fue cancelada',
            'appointment_rescheduled' => 'Tu cita fue reprogramada',
            default => 'Actualizacion de reserva en TREBBIA',
        };
    }
}
