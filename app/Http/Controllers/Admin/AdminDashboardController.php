<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\ManualPayment;
use App\Models\Subscription;
use App\Models\User;
use App\Models\WhatsAppActivationRequest;
use App\Services\PlanCatalog;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function __invoke(PlanCatalog $plans): View
    {
        $plans->sync();

        return view('admin.dashboard', [
            'metrics' => [
                'businesses' => Business::count(),
                'users' => User::count(),
                'trials' => Subscription::where('status', Subscription::STATUS_TRIALING)->count(),
                'activeSubscriptions' => Subscription::where('status', Subscription::STATUS_ACTIVE)->count(),
                'expiredSubscriptions' => Subscription::where('status', Subscription::STATUS_EXPIRED)->count(),
                'whatsappRequests' => WhatsAppActivationRequest::whereNotIn('status', [WhatsAppActivationRequest::STATUS_ACTIVE])->count(),
                'manualRevenueCents' => ManualPayment::where('status', ManualPayment::STATUS_CONFIRMED)->sum('amount_cents'),
            ],
            'recentBusinesses' => Business::with(['owner', 'subscription.plan'])->latest()->take(8)->get(),
            'recentSubscriptions' => Subscription::with(['business.owner', 'plan'])->latest()->take(8)->get(),
            'recentPayments' => ManualPayment::with(['business', 'plan'])->latest('paid_at')->take(6)->get(),
        ]);
    }
}
