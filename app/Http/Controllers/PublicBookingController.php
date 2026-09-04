<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Business;
use App\Services\BookingEngine;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PublicBookingController extends Controller
{
    public function __construct(private BookingEngine $booking) {}

    public function show(Request $request, Business $business): View
    {
        abort_unless($this->publicBookingEnabled($business), 404);

        $settings = $business->settings()->firstOrCreate([]);
        $services = $this->booking->services($business);
        $selectedService = $this->booking->service($business, $request->integer('service_id') ?: null);
        $professionals = $this->booking->professionalsForService($business, $selectedService);
        $selectedProfessional = $professionals->firstWhere('id', $request->integer('professional_id'));
        $date = CarbonImmutable::parse($request->input('date', now($business->timezone)->addDay()->toDateString()), $business->timezone);

        return view('public-booking.show', [
            'business' => $business,
            'settings' => $settings,
            'services' => $services,
            'selectedService' => $selectedService,
            'professionals' => $professionals,
            'professionalOptionsByService' => $this->booking->professionalOptionsByService($business, $services),
            'selectedProfessional' => $selectedProfessional,
            'date' => $date,
            'availableSlots' => $selectedService && $selectedProfessional
                ? $this->booking->availableSlots($business, $selectedService, $selectedProfessional->id, $date)
                : collect(),
        ]);
    }

    public function store(Request $request, Business $business): RedirectResponse
    {
        abort_unless($this->publicBookingEnabled($business), 404);

        $attributes = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where('business_id', $business->id)->where('is_active', true)],
            'professional_id' => ['required', Rule::exists('professionals', 'id')->where('business_id', $business->id)->where('is_active', true)],
            'date' => ['required', 'date'],
            'starts_at' => ['required', 'date_format:H:i'],
            'client_name' => ['required', 'string', 'max:140'],
            'client_email' => ['nullable', 'email', 'max:180'],
            'client_phone' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:800'],
        ]);

        $service = $business->services()->where('is_active', true)->findOrFail($attributes['service_id']);
        $startsAt = CarbonImmutable::parse($attributes['date'].' '.$attributes['starts_at'], $business->timezone);
        $client = $this->booking->findOrCreateClient($business, $attributes);
        $settings = $business->settings()->firstOrCreate([]);
        $requiresConfirmation = (bool) ($settings->public_booking_settings['require_manual_confirmation'] ?? true);

        $appointment = $this->booking->createAppointment($business, [
            'client_id' => $client->id,
            'service_id' => $service->id,
            'professional_id' => $attributes['professional_id'],
            'starts_at' => $startsAt,
            'status' => $requiresConfirmation ? 'scheduled' : 'confirmed',
            'source_channel' => Appointment::SOURCE_PUBLIC_BOOKING,
            'source_metadata' => [
                'client_email' => $attributes['client_email'] ?? null,
                'client_phone' => $attributes['client_phone'] ?? null,
            ],
            'notes' => $attributes['notes'] ?? null,
        ]);

        return redirect()->route('public-booking.confirmation', [$business->slug, 'appointment' => $appointment->id]);
    }

    public function confirmation(Business $business, int $appointment): View
    {
        abort_unless($this->publicBookingEnabled($business), 404);

        $appointment = $business->appointments()
            ->with(['client', 'professional', 'service'])
            ->whereKey($appointment)
            ->firstOrFail();

        return view('public-booking.confirmation', [
            'business' => $business,
            'appointment' => $appointment,
        ]);
    }

    private function publicBookingEnabled(Business $business): bool
    {
        $settings = $business->settings()->firstOrCreate([]);

        return $business->status === 'active'
            && (bool) ($settings->public_booking_settings['allow_public_booking'] ?? false);
    }
}
