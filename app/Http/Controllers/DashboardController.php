<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\BookingRequest;
use App\Services\BookingShareCenter;
use Illuminate\Database\Eloquent\Builder;

class DashboardController extends Controller
{
    public function __invoke(BookingShareCenter $shareCenter)
    {
        $business = app('activeBusiness');
        $today = today($business->timezone);
        $pendingRequestsQuery = $business->bookingRequests()->where('status', BookingRequest::STATUS_PENDING);
        $todayAppointmentsQuery = $business->appointments()->whereDate('starts_at', $today);
        $todayBlockedTimesQuery = $business->blockedTimes()->whereDate('starts_at', $today);
        $todayPendingAppointments = (clone $todayAppointmentsQuery)->where('status', Appointment::STATUS_SCHEDULED)->count();
        $attentionCount = (clone $pendingRequestsQuery)->count() + $todayPendingAppointments;

        return view('dashboard', [
            'business' => $business,
            'todayStatus' => [
                'attention_count' => $attentionCount,
                'state' => $attentionCount > 0 ? 'attention' : 'ok',
                'title' => $attentionCount > 0 ? 'Requiere tu atencion' : 'Todo en orden',
                'message' => $attentionCount > 0
                    ? 'Hay solicitudes o citas que necesitan una decision.'
                    : 'No hay pendientes criticos para hoy.',
            ],
            'metrics' => [
                'todayAppointments' => (clone $todayAppointmentsQuery)->count(),
                'todayConfirmedAppointments' => (clone $todayAppointmentsQuery)->where('status', Appointment::STATUS_CONFIRMED)->count(),
                'todayPendingAppointments' => $todayPendingAppointments,
                'upcomingAppointments' => $business->appointments()->where('starts_at', '>=', now())->count(),
                'pendingRequests' => (clone $pendingRequestsQuery)->count(),
                'todayBlockedTimes' => (clone $todayBlockedTimesQuery)->count(),
                'todayProfessionals' => $business->appointments()
                    ->whereDate('starts_at', $today)
                    ->whereNotNull('professional_id')
                    ->distinct('professional_id')
                    ->count('professional_id'),
                'clients' => $business->clients()->count(),
                'professionals' => $business->professionals()->where('is_active', true)->count(),
                'services' => $business->services()->where('is_active', true)->count(),
            ],
            'share' => $shareCenter->for($business),
            'todayAppointments' => $business->appointments()
                ->with(['client', 'professional', 'service'])
                ->whereDate('starts_at', $today)
                ->orderBy('starts_at')
                ->take(6)
                ->get(),
            'todayBlockedTimes' => $business->blockedTimes()
                ->with(['professional', 'resource'])
                ->whereDate('starts_at', $today)
                ->orderBy('starts_at')
                ->take(5)
                ->get(),
            'upcomingAppointments' => $business->appointments()
                ->with(['client', 'professional', 'service'])
                ->where('starts_at', '>=', now())
                ->whereDate('starts_at', '!=', $today)
                ->orderBy('starts_at')
                ->take(5)
                ->get(),
            'pendingBookingRequests' => $business->bookingRequests()
                ->with(['client', 'professional', 'service'])
                ->where('status', BookingRequest::STATUS_PENDING)
                ->when(request('attention') === 'today', fn (Builder $query): Builder => $query->whereDate('starts_at', $today))
                ->orderBy('starts_at')
                ->take(5)
                ->get(),
        ]);
    }
}
