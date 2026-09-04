<?php

namespace App\Http\Controllers;

use App\Services\BookingShareCenter;

class DashboardController extends Controller
{
    public function __invoke(BookingShareCenter $shareCenter)
    {
        $business = app('activeBusiness');

        return view('dashboard', [
            'business' => $business,
            'metrics' => [
                'todayAppointments' => $business->appointments()->whereDate('starts_at', today())->count(),
                'upcomingAppointments' => $business->appointments()->where('starts_at', '>=', now())->count(),
                'clients' => $business->clients()->count(),
                'professionals' => $business->professionals()->where('is_active', true)->count(),
                'services' => $business->services()->where('is_active', true)->count(),
            ],
            'share' => $shareCenter->for($business),
            'upcomingAppointments' => $business->appointments()
                ->with(['client', 'professional', 'service'])
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(5)
                ->get(),
        ]);
    }
}
