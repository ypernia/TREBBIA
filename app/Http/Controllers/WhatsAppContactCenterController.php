<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BookingRequest;
use App\Services\BookingShareCenter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class WhatsAppContactCenterController extends Controller
{
    public function __invoke(BookingShareCenter $shareCenter): View
    {
        $business = app('activeBusiness');
        $followUps = $this->followUps();
        $pendingRequests = $business->bookingRequests()
            ->with(['client', 'professional', 'service'])
            ->where('status', BookingRequest::STATUS_PENDING)
            ->orderBy('starts_at')
            ->take(12)
            ->get();
        $noResponse = $followUps->filter(fn (Appointment $appointment): bool => $appointment->contactStatus() === Appointment::CONTACT_NO_RESPONSE);
        $callRequired = $followUps->filter(fn (Appointment $appointment): bool => $appointment->contactStatus() === Appointment::CONTACT_CALL_REQUIRED);

        return view('whatsapp-contact.index', [
            'business' => $business,
            'share' => $shareCenter->for($business),
            'followUps' => $followUps,
            'pendingRequests' => $pendingRequests,
            'urgent' => $followUps
                ->filter(fn (Appointment $appointment): bool => in_array($appointment->contactStatus(), [
                    Appointment::CONTACT_PENDING,
                    Appointment::CONTACT_CALL_REQUIRED,
                ], true))
                ->values(),
            'noResponse' => $noResponse->values(),
            'callRequired' => $callRequired->values(),
            'metrics' => [
                'followUps' => $followUps->count(),
                'pendingRequests' => $pendingRequests->count(),
                'noResponse' => $noResponse->count(),
                'callRequired' => $callRequired->count(),
            ],
        ]);
    }

    private function followUps()
    {
        return app('activeBusiness')->appointments()
            ->with(['client', 'professional', 'service'])
            ->where('starts_at', '>=', now(app('activeBusiness')->timezone)->subDays(2))
            ->where(function (Builder $query): void {
                $query
                    ->where('source_metadata->reschedule_contact_pending', true)
                    ->orWhereIn('source_metadata->reschedule_contact_status', [
                        Appointment::CONTACT_PENDING,
                        Appointment::CONTACT_SENT,
                        Appointment::CONTACT_NO_RESPONSE,
                        Appointment::CONTACT_CALL_REQUIRED,
                    ]);
            })
            ->orderBy('starts_at')
            ->take(24)
            ->get();
    }
}
