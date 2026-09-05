<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\PlatformAuditLog;
use App\Services\PlanCatalog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminBusinessController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        return view('admin.businesses.index', [
            'search' => $search,
            'businesses' => Business::query()
                ->with(['owner', 'subscription.plan'])
                ->withCount(['clients', 'appointments', 'professionals'])
                ->when($search, fn (Builder $query): Builder => $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhereHas('owner', fn (Builder $query): Builder => $query->where('email', 'like', "%{$search}%"));
                }))
                ->latest()
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function show(Business $business, PlanCatalog $planCatalog): View
    {
        $business->load(['owner', 'subscription.plan']);
        $business->loadCount([
            'appointments',
            'branches',
            'businessUsers',
            'clients',
            'conversationMessages',
            'conversations',
            'professionals',
            'resources',
            'services',
            'whatsappActivationRequests',
        ]);

        return view('admin.businesses.show', [
            'business' => $business,
            'plans' => $planCatalog->sync(),
            'statusLabels' => $this->statusLabels(),
            'payments' => $business->manualPayments()
                ->with(['plan', 'recordedBy'])
                ->latest('paid_at')
                ->take(8)
                ->get(),
            'events' => $business->subscriptionEvents()
                ->with(['fromPlan', 'toPlan'])
                ->latest()
                ->take(12)
                ->get(),
            'auditLogs' => PlatformAuditLog::with('user')
                ->where('business_id', $business->id)
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }

    private function statusLabels(): array
    {
        return [
            'trialing' => 'Prueba',
            'active' => 'Activa',
            'past_due' => 'Pago pendiente',
            'expired' => 'Expirada',
            'cancelled' => 'Cancelada',
            'suspended' => 'Suspendida',
        ];
    }
}
