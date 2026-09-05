<?php

namespace App\Http\Controllers;

use App\Services\BookingShareCenter;
use App\Services\PlanEntitlements;
use Illuminate\View\View;

class SharingController extends Controller
{
    public function __invoke(BookingShareCenter $shareCenter): View
    {
        $business = app('activeBusiness');
        abort_unless(app(PlanEntitlements::class)->can($business, 'public_booking.enabled'), 403);

        return view('sharing.index', [
            'business' => $business,
            'share' => $shareCenter->for($business),
        ]);
    }
}
