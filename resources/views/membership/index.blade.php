@extends('layouts.app')

@section('title', 'Membresia | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Membresia')

@section('content')
    @php
        $money = fn (int $cents): string => $cents === 0 ? 'Gratis' : '$'.number_format($cents / 100, 0, ',', '.').'/mes';
        $formatLimit = function ($limit, $used): string {
            if (is_bool($limit)) {
                return $limit ? 'Incluido' : 'No incluido';
            }

            return $limit ? "{$used} / {$limit}" : "{$used} / ilimitado";
        };
    @endphp

    <div class="mb-5 grid gap-4 xl:grid-cols-[1fr_24rem]">
        <section class="trebbia-card p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-sm font-bold text-[#245f57]">{{ $statusLabels[$subscription->status] ?? ucfirst($subscription->status) }}</p>
                    <h2 class="mt-2 text-2xl font-bold">{{ $currentPlan?->name ?: 'Sin plan' }}</h2>
                    <p class="mt-2 text-sm text-[#64716d]">Periodo actual hasta {{ $subscription->current_period_ends_at?->format('d/m/Y') ?: 'sin fecha definida' }}.</p>
                </div>
                <p class="text-2xl font-bold">{{ $money($currentPlan?->monthly_price_cents ?? 0) }}</p>
            </div>
        </section>

        <section class="trebbia-card p-6">
            <h2 class="text-lg font-bold">Estado comercial</h2>
            <div class="mt-4 space-y-3 text-sm text-[#53615d]">
                <p><span class="font-bold text-[#18211f]">Suscripcion:</span> {{ $statusLabels[$subscription->status] ?? ucfirst($subscription->status) }}</p>
                <p><span class="font-bold text-[#18211f]">Prueba hasta:</span> {{ $subscription->trial_ends_at?->format('d/m/Y') ?: 'Sin prueba' }}</p>
                <p><span class="font-bold text-[#18211f]">Plan:</span> {{ $currentPlan?->code ?: 'sin-plan' }}</p>
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <main class="space-y-6">
            <section class="trebbia-card p-6">
                <h2 class="text-xl font-bold">Uso del plan</h2>
                <div class="mt-5 space-y-4">
                    @foreach ($limits as $item)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                <p class="font-bold">{{ $item['label'] }}</p>
                                <p class="{{ $item['is_enabled'] ? 'text-[#64716d]' : 'font-bold text-[#8a3027]' }}">{{ $formatLimit($item['limit'], $item['used']) }}</p>
                            </div>
                            @if (is_bool($item['limit']))
                                <div class="rounded-md border {{ $item['is_enabled'] ? 'border-[#cfe4da] bg-[#edf7f4] text-[#245f57]' : 'border-[#f0c9c4] bg-[#fff4f2] text-[#8a3027]' }} px-3 py-2 text-sm font-semibold">
                                    {{ $item['is_enabled'] ? 'Disponible en este plan' : 'No disponible en este plan' }}
                                </div>
                            @else
                                <div class="h-3 overflow-hidden rounded-full bg-[#edf2ef]">
                                    <div class="h-full rounded-full {{ $item['percent'] >= 90 ? 'bg-[#8a3027]' : 'bg-[#245f57]' }}" style="width: {{ $item['percent'] }}%"></div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-6">
                    <h2 class="text-xl font-bold">Planes disponibles</h2>
                    <p class="mt-1 text-sm text-[#64716d]">Base comercial lista para conectar pagos en una fase posterior.</p>
                </div>

                <div class="grid gap-0 lg:grid-cols-3">
                    @foreach ($plans as $plan)
                        <div class="border-b border-[#e7ebe7] p-5 lg:border-r">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-lg font-bold">{{ $plan->name }}</h3>
                                    <p class="mt-1 text-sm text-[#64716d]">{{ $plan->code }}</p>
                                </div>
                                @if ($currentPlan?->id === $plan->id)
                                    <span class="rounded-md bg-[#edf7f4] px-2 py-1 text-xs font-bold text-[#245f57]">Actual</span>
                                @endif
                            </div>
                            <p class="mt-4 text-2xl font-bold">{{ $money($plan->monthly_price_cents) }}</p>
                            <div class="mt-4 space-y-2 text-sm text-[#53615d]">
                                @foreach ($plan->features ?? [] as $feature)
                                    <p>{{ $feature }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>

        <aside class="trebbia-card p-6">
            <h2 class="text-lg font-bold">Cambiar plan</h2>
            <form method="POST" action="{{ route('membership.update') }}" class="mt-5 grid gap-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="trebbia-label" for="plan_id">Plan</label>
                    <select class="trebbia-input" id="plan_id" name="plan_id" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->id }}" @selected($currentPlan?->id === $plan->id)>{{ $plan->name }} - {{ $money($plan->monthly_price_cents) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="trebbia-label" for="status">Estado</label>
                    <select class="trebbia-input" id="status" name="status" required>
                        @foreach ($statusLabels as $value => $label)
                            <option value="{{ $value }}" @selected($subscription->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <button class="trebbia-button">Actualizar membresia</button>
            </form>

            <div class="mt-6 rounded-md border border-[#e1e6e0] bg-[#f8faf8] p-4 text-sm text-[#64716d]">
                Esta accion es manual por ahora. La pasarela de pago podra actualizar estos datos automaticamente mas adelante.
            </div>
        </aside>
    </div>
@endsection
