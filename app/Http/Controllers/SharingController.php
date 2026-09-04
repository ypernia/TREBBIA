<?php

namespace App\Http\Controllers;

use App\Services\BookingShareCenter;
use Illuminate\View\View;

class SharingController extends Controller
{
    public function __invoke(BookingShareCenter $shareCenter): View
    {
        $business = app('activeBusiness');

        return view('sharing.index', [
            'business' => $business,
            'share' => $shareCenter->for($business),
        ]);
    }
}
