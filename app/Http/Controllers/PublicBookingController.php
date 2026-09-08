<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BookingRequest;
use App\Models\Business;
use App\Services\BookingEngine;
use App\Services\SubscriptionManager;
use App\Support\TimeInput;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            'bookingAlternatives' => session('booking_alternatives', []),
        ]);
    }

    public function store(Request $request, Business $business): RedirectResponse
    {
        abort_unless($this->publicBookingEnabled($business), 404);

        $attributes = $request->validate([
            'service_id' => ['required', Rule::exists('services', 'id')->where('business_id', $business->id)->where('is_active', true)],
            'professional_id' => ['required', Rule::exists('professionals', 'id')->where('business_id', $business->id)->where('is_active', true)],
            'date' => ['required', 'date'],
            'starts_at' => ['required', TimeInput::VALIDATION_RULE],
            'client_name' => ['required', 'string', 'max:140'],
            'client_email' => ['nullable', 'email', 'max:180'],
            'client_phone' => ['nullable', 'string', 'max:60'],
            'notes' => ['nullable', 'string', 'max:800'],
        ], [
            'starts_at.required' => 'Selecciona un horario disponible.',
            'starts_at.date_format' => 'El horario seleccionado debe ser valido, por ejemplo 09:00.',
            'client_name.required' => 'Indica tu nombre para reservar.',
        ], [
            'starts_at' => 'horario',
            'client_name' => 'nombre',
            'client_email' => 'correo',
            'client_phone' => 'telefono',
            'service_id' => 'servicio',
            'professional_id' => 'profesional',
            'date' => 'fecha',
        ]);

        $service = $business->services()->where('is_active', true)->findOrFail($attributes['service_id']);
        $attributes['starts_at'] = TimeInput::normalize($attributes['starts_at']);
        $startsAt = CarbonImmutable::parse($attributes['date'].' '.$attributes['starts_at'], $business->timezone);
        $client = $this->booking->findOrCreateClient($business, $attributes);
        $settings = $business->settings()->firstOrCreate([]);
        $requiresConfirmation = (bool) ($settings->public_booking_settings['require_manual_confirmation'] ?? true);

        try {
            if ($requiresConfirmation) {
                $bookingRequest = $this->booking->createBookingRequest($business, [
                    'client_id' => $client->id,
                    'service_id' => $service->id,
                    'professional_id' => $attributes['professional_id'],
                    'starts_at' => $startsAt,
                    'source_channel' => Appointment::SOURCE_PUBLIC_BOOKING,
                    'source_reference' => $this->publicSourceReference($business, $attributes),
                    'source_metadata' => [
                        'client_email' => $attributes['client_email'] ?? null,
                        'client_phone' => $attributes['client_phone'] ?? null,
                    ],
                    'notes' => $attributes['notes'] ?? null,
                ]);

                return redirect()->route('public-booking.request-confirmation', [$business->slug, 'bookingRequest' => $bookingRequest->id]);
            }

            $appointment = $this->booking->createAppointment($business, [
                'client_id' => $client->id,
                'service_id' => $service->id,
                'professional_id' => $attributes['professional_id'],
                'starts_at' => $startsAt,
                'status' => Appointment::STATUS_CONFIRMED,
                'source_channel' => Appointment::SOURCE_PUBLIC_BOOKING,
                'source_reference' => $this->publicSourceReference($business, $attributes),
                'source_metadata' => [
                    'client_email' => $attributes['client_email'] ?? null,
                    'client_phone' => $attributes['client_phone'] ?? null,
                ],
                'notes' => $attributes['notes'] ?? null,
            ]);
        } catch (ValidationException) {
            $alternatives = $this->booking->alternativeSlots(
                $business,
                $service,
                (int) $attributes['professional_id'],
                CarbonImmutable::parse($attributes['date'], $business->timezone),
                null,
                $attributes['starts_at'],
            )->map->format('H:i')->all();

            return redirect()
                ->route('public-booking.show', [
                    'business' => $business->slug,
                    'service_id' => $attributes['service_id'],
                    'professional_id' => $attributes['professional_id'],
                    'date' => $attributes['date'],
                ])
                ->withInput()
                ->withErrors(['starts_at' => 'Ese horario acaba de ser reservado. Encontramos otras opciones disponibles.'])
                ->with('booking_alternatives', $alternatives);
        }

        return redirect()->route('public-booking.confirmation', [$business->slug, 'appointment' => $appointment->id]);
    }

    public function requestConfirmation(Business $business, BookingRequest $bookingRequest): View
    {
        abort_unless($this->publicBookingEnabled($business), 404);
        abort_unless($bookingRequest->business_id === $business->id, 404);

        $bookingRequest->load(['client', 'professional', 'service']);

        return view('public-booking.confirmation', [
            'business' => $business,
            'appointment' => null,
            'bookingRequest' => $bookingRequest,
        ]);
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
            'bookingRequest' => null,
        ]);
    }

    private function publicBookingEnabled(Business $business): bool
    {
        $settings = $business->settings()->firstOrCreate([]);
        $subscription = app(SubscriptionManager::class)->ensure($business);

        return $business->status === 'active'
            && $subscription->hasOperationalAccess()
            && (bool) ($settings->public_booking_settings['allow_public_booking'] ?? false);
    }

    private function publicSourceReference(Business $business, array $attributes): string
    {
        return 'public:'.hash('sha256', implode('|', [
            $business->id,
            $attributes['service_id'],
            $attributes['professional_id'],
            $attributes['date'],
            $attributes['starts_at'],
            strtolower((string) ($attributes['client_email'] ?? '')),
            preg_replace('/\D+/', '', (string) ($attributes['client_phone'] ?? '')),
            mb_strtolower(trim((string) $attributes['client_name'])),
        ]));
    }
}
