@extends('layouts.app')

@section('title', 'Membresia | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Membresia')

@section('content')
    @php
        $money = fn (?int $cents): string => $cents === null ? 'A definir' : '$'.number_format($cents / 100, 0, ',', '.');
        $monthly = fn (?int $cents): string => $cents === null ? 'A definir' : $money($cents).'/mes';
        $formatLimit = function ($item): string {
            if (str_contains($item['key'], '.')) {
                return $item['is_enabled'] ? 'Incluido' : 'No incluido';
            }

            return $item['limit'] === null ? 'Ilimitado' : (string) $item['limit'];
        };
        $isLocked = ! $subscription->hasOperationalAccess();
    @endphp

    @if ($subscription->isTrialing())
        <section class="mb-5 rounded-md border border-[#cfe4da] bg-[#edf7f4] p-4 text-sm text-[#245f57]">
            <p class="font-bold">Estas usando TREBBIA en periodo de prueba.</p>
            <p class="mt-1">Te quedan {{ $subscription->trialDaysRemaining() }} dia(s) de los {{ $trialDays }} dias disponibles. Al finalizar, conserva tus datos y selecciona una membresia paga para continuar operando.</p>
        </section>
    @elseif ($isLocked)
        <section class="mb-5 rounded-md border border-[#f0c9c4] bg-[#fff4f2] p-4 text-sm text-[#8a3027]">
            <p class="font-bold">Tu acceso operativo esta pausado.</p>
            <p class="mt-1">Tus clientes, citas, servicios, profesionales y configuracion se conservan. Selecciona una membresia para continuar utilizando TREBBIA.</p>
        </section>
    @endif

    <div class="mb-6 grid gap-4 xl:grid-cols-[1fr_24rem]">
        <section class="trebbia-card p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#245f57]">{{ $statusLabels[$subscription->status] ?? ucfirst($subscription->status) }}</p>
                    <h2 class="mt-2 text-3xl font-bold">{{ $currentPlan?->name ?: 'Prueba gratuita' }}</h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-[#64716d]">
                        {{ $currentPlan?->description ?: 'Prueba temporal para configurar el negocio, probar reservas, agenda, clientes y canales antes de elegir una membresia paga.' }}
                    </p>
                </div>
                <div class="rounded-md border border-[#e1e6e0] bg-[#f8faf8] p-4 text-right">
                    <p class="text-sm font-bold text-[#64716d]">Valor actual</p>
                    <p class="mt-1 text-2xl font-bold">{{ $subscription->isTrialing() ? 'Trial' : $monthly($currentPlan?->monthly_price_cents) }}</p>
                </div>
            </div>
        </section>

        <section class="trebbia-card p-6">
            <h2 class="text-lg font-bold">Estado comercial</h2>
            <div class="mt-4 space-y-3 text-sm text-[#53615d]">
                <p><span class="font-bold text-[#18211f]">Suscripcion:</span> {{ $statusLabels[$subscription->status] ?? ucfirst($subscription->status) }}</p>
                <p><span class="font-bold text-[#18211f]">Trial hasta:</span> {{ $subscription->trial_ends_at?->format('d/m/Y') ?: 'No aplica' }}</p>
                <p><span class="font-bold text-[#18211f]">Renovacion:</span> {{ $subscription->current_period_ends_at?->format('d/m/Y') ?: 'Pendiente de activacion' }}</p>
            </div>
        </section>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <main class="space-y-6">
            <section class="trebbia-card p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Uso actual</h2>
                        <p class="mt-1 text-sm text-[#64716d]">Los limites se calculan desde una capa central de capacidades por negocio.</p>
                    </div>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach ($limits as $item)
                        <div class="rounded-md border border-[#e1e6e0] p-4">
                            <div class="flex items-start justify-between gap-3">
                                <p class="font-bold">{{ $item['label'] }}</p>
                                <p class="{{ $item['is_enabled'] ? 'text-[#64716d]' : 'font-bold text-[#8a3027]' }}">{{ $formatLimit($item) }}</p>
                            </div>
                            @if (! str_contains($item['key'], '.'))
                                <p class="mt-2 text-sm text-[#64716d]">Uso: {{ $item['used'] }} de {{ $item['limit'] ?? 'ilimitado' }}</p>
                                <div class="mt-3 h-2 overflow-hidden rounded-full bg-[#edf2ef]">
                                    <div class="h-full rounded-full {{ $item['percent'] >= 90 ? 'bg-[#8a3027]' : 'bg-[#245f57]' }}" style="width: {{ $item['percent'] }}%"></div>
                                </div>
                            @else
                                <p class="mt-2 text-sm text-[#64716d]">{{ $item['is_enabled'] ? 'Disponible para este estado o plan.' : 'Disponible al subir de membresia.' }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-6">
                    <h2 class="text-xl font-bold">Planes pagos</h2>
                    <p class="mt-1 text-sm text-[#64716d]">TREBBIA no opera con plan gratuito permanente. La prueba permite conocer el producto; la continuidad requiere una membresia activa.</p>
                </div>

                <div class="grid gap-0 lg:grid-cols-3">
                    @foreach ($plans as $plan)
                        @php($comparison = $planLimits($plan))
                        <article class="border-b border-[#e7ebe7] p-5 lg:border-r">
                            <div class="flex min-h-24 items-start justify-between gap-3">
                                <div>
                                    <h3 class="text-xl font-bold">{{ $plan->name }}</h3>
                                    <p class="mt-2 text-sm leading-6 text-[#64716d]">{{ $plan->description }}</p>
                                </div>
                                @if ($currentPlan?->id === $plan->id && $subscription->status === 'active')
                                    <span class="rounded-md bg-[#edf7f4] px-2 py-1 text-xs font-bold text-[#245f57]">Actual</span>
                                @endif
                            </div>
                            <p class="mt-4 text-2xl font-bold">{{ $monthly($plan->monthly_price_cents) }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">Anual: {{ $money($plan->annual_price_cents) }}</p>
                            <div class="mt-5 space-y-2 text-sm text-[#53615d]">
                                @foreach (array_slice($plan->features ?? [], 0, 5) as $feature)
                                    <p>{{ $feature }}</p>
                                @endforeach
                            </div>
                            <div class="mt-5 border-t border-[#e7ebe7] pt-4 text-sm text-[#64716d]">
                                @foreach (array_slice($comparison, 0, 6) as $item)
                                    <p class="flex justify-between gap-3 py-1">
                                        <span>{{ $item['label'] }}</span>
                                        <span class="font-bold text-[#18211f]">{{ $formatLimit($item) }}</span>
                                    </p>
                                @endforeach
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </main>

        <aside class="space-y-5">
            <section class="trebbia-card p-6">
                <h2 class="text-lg font-bold">Activar membresia</h2>
                <p class="mt-2 text-sm leading-6 text-[#64716d]">Mientras conectamos pagos, aqui se registra el plan elegido. La activacion real quedara pendiente de pago o validacion administrativa.</p>
                <form method="POST" action="{{ route('membership.update') }}" class="mt-5 grid gap-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="trebbia-label" for="plan_id">Plan pago</label>
                        <select class="trebbia-input" id="plan_id" name="plan_id" required>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" @selected($currentPlan?->id === $plan->id)>{{ $plan->name }} - {{ $monthly($plan->monthly_price_cents) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="trebbia-button">Solicitar membresia</button>
                </form>
            </section>

            <section class="trebbia-card p-6">
                <h2 class="text-lg font-bold">Pendiente de pagos</h2>
                <p class="mt-2 text-sm leading-6 text-[#64716d]">La arquitectura ya queda lista para conectar Wompi, Mercado Pago, Stripe u otra pasarela cuando lo apruebes. Hasta entonces, TREBBIA no activa acceso pago automaticamente.</p>
            </section>
        </aside>
    </div>
@endsection
