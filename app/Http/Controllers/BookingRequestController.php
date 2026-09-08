<?php

namespace App\Http\Controllers;

use App\Models\BookingRequest;
use App\Services\AppointmentNotificationService;
use App\Services\BookingEngine;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BookingRequestController extends Controller
{
    public function accept(Request $request, BookingRequest $bookingRequest, BookingEngine $booking, AppointmentNotificationService $notifications): RedirectResponse
    {
        $this->authorizeTenant($bookingRequest);

        $attributes = $request->validate([
            'decision_notes' => ['nullable', 'string', 'max:800'],
        ]);

        try {
            $appointment = $booking->approveBookingRequest(
                $bookingRequest,
                $request->user()->id,
                $attributes['decision_notes'] ?? null,
            );
        } catch (ValidationException $exception) {
            $alternatives = $booking->alternativeSlots(
                app('activeBusiness'),
                $bookingRequest->service,
                $bookingRequest->professional_id,
                CarbonImmutable::parse($bookingRequest->starts_at, app('activeBusiness')->timezone),
                $bookingRequest->resource_id,
                $bookingRequest->starts_at->format('H:i'),
            )->map->format('H:i')->all();

            return back()
                ->withErrors(['booking_request' => 'Ese horario ya no esta disponible. Revisa opciones antes de aprobar.'])
                ->with('booking_request_alternatives', $alternatives)
                ->with('booking_request_id', $bookingRequest->id);
        }

        $notifications->appointmentConfirmed($appointment);

        return redirect()
            ->route('agenda.index', ['date' => $appointment->starts_at->toDateString()])
            ->with('status', 'Solicitud aceptada y cita confirmada.');
    }

    public function reject(Request $request, BookingRequest $bookingRequest, BookingEngine $booking, AppointmentNotificationService $notifications): RedirectResponse
    {
        $this->authorizeTenant($bookingRequest);

        $attributes = $request->validate([
            'decision_notes' => ['nullable', 'string', 'max:800'],
        ]);

        $rejectedRequest = $booking->rejectBookingRequest(
            $bookingRequest,
            $request->user()->id,
            $attributes['decision_notes'] ?? null,
        );

        $notifications->bookingRejected($rejectedRequest);

        return back()->with('status', 'Solicitud rechazada.');
    }

    private function authorizeTenant(BookingRequest $bookingRequest): void
    {
        abort_unless($bookingRequest->business_id === app('activeBusiness')->id, 404);
    }
}
