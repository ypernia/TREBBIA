<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\PlanCatalog;
use Illuminate\View\View;

class AdminPlanController extends Controller
{
    public function index(PlanCatalog $plans): View
    {
        return view('admin.plans.index', [
            'plans' => $plans->sync(),
        ]);
    }
}
