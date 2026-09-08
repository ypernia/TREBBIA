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

class PublicBookingReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Business $business,
        public BookingRequest|Appointment $reservation,
        public bool $requiresManualConfirmation,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->requiresManualConfirmation
            ? 'Nueva solicitud de reserva en TREBBIA'
            : 'Nueva cita confirmada en TREBBIA';

        return new Envelope(
            from: new Address(
                config('mail.booking_from.address'),
                config('mail.booking_from.name'),
            ),
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.public-booking-received');
    }
}
