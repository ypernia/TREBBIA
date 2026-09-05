@extends('layouts.admin')

@section('title', 'Pagos | TREBBIA ADMIN')
@section('page-title', 'Pagos manuales')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[24rem_1fr]">
        <aside class="trebbia-card p-6">
            <h2 class="text-lg font-bold">Registrar pago confirmado</h2>
            <p class="mt-2 text-sm leading-6 text-[#64748b]">Usa este flujo solo cuando el pago ya fue verificado por TREBBIA. Al guardar, se activa la membresia del negocio.</p>

            <form method="GET" action="{{ route('admin.payments.index') }}" class="mt-5 flex gap-2">
                <input class="trebbia-input" name="q" value="{{ $search }}" placeholder="Buscar negocio">
                <button class="trebbia-button trebbia-button-secondary">Buscar</button>
            </form>

            <form method="POST" action="{{ route('admin.payments.store') }}" class="mt-5 grid gap-4">
                @csrf
                <div>
                    <label class="trebbia-label" for="business_id">Business</label>
                    <select class="trebbia-input" id="business_id" name="business_id" required>
                        <option value="">Seleccionar negocio</option>
                        @foreach ($businesses as $business)
                            <option value="{{ $business->id }}" @selected(old('business_id') == $business->id)>
                                {{ $business->name }} - {{ $business->owner?->email }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="trebbia-label" for="plan_id">Plan pago</label>
                    <select class="trebbia-input" id="plan_id" name="plan_id" required>
                        <option value="">Seleccionar plan</option>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected(old('plan_id') == $plan->id)>
                                {{ $plan->name }} - ${{ number_format($plan->monthly_price_cents / 100, 0, ',', '.') }}/mes
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="trebbia-label" for="amount">Valor recibido</label>
                        <input class="trebbia-input" id="amount" name="amount" type="number" min="1" step="1" value="{{ old('amount') }}" required>
                    </div>
                    <div>
                        <label class="trebbia-label" for="currency">Moneda</label>
                        <select class="trebbia-input" id="currency" name="currency" required>
                            @foreach (['COP', 'USD', 'MXN', 'PEN', 'CLP', 'EUR'] as $currency)
                                <option value="{{ $currency }}" @selected(old('currency', 'COP') === $currency)>{{ $currency }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="trebbia-label" for="period_months">Periodo</label>
                        <select class="trebbia-input" id="period_months" name="period_months" required>
                            @foreach ([1, 3, 6, 12] as $months)
                                <option value="{{ $months }}" @selected(old('period_months', 1) == $months)>{{ $months }} mes(es)</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="trebbia-label" for="paid_at">Fecha pago</label>
                        <input class="trebbia-input" id="paid_at" name="paid_at" type="datetime-local" value="{{ old('paid_at', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>
                </div>

                <div>
                    <label class="trebbia-label" for="payment_method">Metodo</label>
                    <select class="trebbia-input" id="payment_method" name="payment_method" required>
                        @foreach ($methods as $value => $label)
                            <option value="{{ $value }}" @selected(old('payment_method') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="trebbia-label" for="reference">Referencia</label>
                    <input class="trebbia-input" id="reference" name="reference" value="{{ old('reference') }}" placeholder="Comprobante, transaccion o nota corta">
                </div>

                <div>
                    <label class="trebbia-label" for="notes">Motivo / soporte interno</label>
                    <textarea class="trebbia-input min-h-24" id="notes" name="notes" placeholder="Ej: Pago confirmado por transferencia bancaria.">{{ old('notes') }}</textarea>
                </div>

                <button class="trebbia-button">Registrar y activar</button>
            </form>
        </aside>

        <main class="space-y-5">
            @include('partials.errors')

            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <h2 class="text-lg font-bold">Pagos registrados</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[58rem] text-left text-sm">
                        <thead class="bg-[#f8fafc] text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">
                            <tr>
                                <th class="px-5 py-3">Business</th>
                                <th class="px-5 py-3">Plan</th>
                                <th class="px-5 py-3">Valor</th>
                                <th class="px-5 py-3">Metodo</th>
                                <th class="px-5 py-3">Registrado por</th>
                                <th class="px-5 py-3">Fecha</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7ebe7]">
                            @forelse ($payments as $payment)
                                <tr>
                                    <td class="px-5 py-4">
                                        <p class="font-bold">{{ $payment->business?->name }}</p>
                                        <p class="text-[#64748b]">{{ $payment->business?->owner?->email }}</p>
                                    </td>
                                    <td class="px-5 py-4">{{ $payment->plan?->name }}</td>
                                    <td class="px-5 py-4 font-bold">${{ number_format($payment->amount_cents / 100, 0, ',', '.') }} {{ $payment->currency }}</td>
                                    <td class="px-5 py-4">
                                        <p>{{ $methods[$payment->payment_method] ?? $payment->payment_method }}</p>
                                        <p class="text-[#64748b]">{{ $payment->reference ?: 'Sin referencia' }}</p>
                                    </td>
                                    <td class="px-5 py-4">{{ $payment->recordedBy?->email }}</td>
                                    <td class="px-5 py-4">{{ $payment->paid_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-5 py-8 text-center text-[#64748b]">Sin pagos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <div>{{ $payments->links() }}</div>
        </main>
    </div>
@endsection
