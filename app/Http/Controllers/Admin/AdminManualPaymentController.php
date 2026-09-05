<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\ManualPayment;
use App\Models\PlatformAuditLog;
use App\Models\Plan;
use App\Services\PlanCatalog;
use App\Services\SubscriptionManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminManualPaymentController extends Controller
{
    public function index(Request $request, PlanCatalog $planCatalog): View
    {
        $search = $request->string('q')->toString();

        return view('admin.payments.index', [
            'search' => $search,
            'plans' => $planCatalog->sync(),
            'businesses' => Business::with(['owner', 'subscription.plan'])
                ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhereHas('owner', fn ($query) => $query->where('email', 'like', "%{$search}%"));
                }))
                ->orderBy('name')
                ->take(20)
                ->get(),
            'payments' => ManualPayment::with(['business.owner', 'plan', 'recordedBy'])
                ->latest('paid_at')
                ->latest()
                ->paginate(15),
            'methods' => $this->paymentMethods(),
        ]);
    }

    public function store(Request $request, SubscriptionManager $subscriptions): RedirectResponse
    {
        $attributes = $request->validate([
            'business_id' => ['required', Rule::exists('businesses', 'id')],
            'plan_id' => ['required', Rule::exists('plans', 'id')->where('is_active', true)],
            'amount' => ['required', 'numeric', 'min:1'],
            'currency' => ['required', Rule::in(['COP', 'USD', 'MXN', 'PEN', 'CLP', 'EUR'])],
            'period_months' => ['required', 'integer', 'min:1', 'max:24'],
            'payment_method' => ['required', Rule::in(array_keys($this->paymentMethods()))],
            'reference' => ['nullable', 'string', 'max:140'],
            'paid_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:800'],
        ]);

        $business = Business::findOrFail($attributes['business_id']);
        $plan = Plan::where('is_active', true)->findOrFail($attributes['plan_id']);
        abort_unless($plan->monthly_price_cents > 0, 422, 'Solo se pueden registrar pagos contra planes pagos.');

        $subscription = $subscriptions->ensure($business);

        $payment = $business->manualPayments()->create([
            'subscription_id' => $subscription->id,
            'plan_id' => $plan->id,
            'recorded_by' => $request->user()->id,
            'status' => ManualPayment::STATUS_CONFIRMED,
            'currency' => $attributes['currency'],
            'amount_cents' => (int) round($attributes['amount'] * 100),
            'period_months' => (int) $attributes['period_months'],
            'payment_method' => $attributes['payment_method'],
            'reference' => $attributes['reference'] ?? null,
            'paid_at' => $attributes['paid_at'],
            'notes' => $attributes['notes'] ?? null,
        ]);

        $activated = $subscriptions->activate($subscription, $plan);
        $activated->update([
            'current_period_started_at' => now(),
            'current_period_ends_at' => now()->addMonths((int) $attributes['period_months']),
        ]);

        PlatformAuditLog::record(
            $request,
            'manual_payment.confirmed',
            $payment,
            [
                'amount_cents' => $payment->amount_cents,
                'currency' => $payment->currency,
                'period_months' => $payment->period_months,
                'plan_id' => $plan->id,
            ],
            $business->id,
            $payment->notes ?: 'Pago manual confirmado',
        );

        return redirect()->route('admin.payments.index')->with('status', 'Pago registrado y membresia activada.');
    }

    private function paymentMethods(): array
    {
        return [
            'bank_transfer' => 'Transferencia bancaria',
            'cash' => 'Efectivo',
            'card_terminal' => 'Datáfono',
            'nequi' => 'Nequi',
            'daviplata' => 'Daviplata',
            'other' => 'Otro',
        ];
    }
}
