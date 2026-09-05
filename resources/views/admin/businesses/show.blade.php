@extends('layouts.admin')

@section('title', $business->name.' | TREBBIA ADMIN')
@section('page-title', 'Ficha del business')

@section('content')
    @php
        $subscription = $business->subscription;
        $usage = [
            'Clientes' => $business->clients_count,
            'Citas' => $business->appointments_count,
            'Profesionales' => $business->professionals_count,
            'Servicios' => $business->services_count,
            'Recursos' => $business->resources_count,
            'Sedes' => $business->branches_count,
            'Usuarios' => $business->business_users_count,
            'Conversaciones' => $business->conversations_count,
            'Mensajes WhatsApp' => $business->conversation_messages_count,
            'Solicitudes WhatsApp' => $business->whatsapp_activation_requests_count,
        ];
    @endphp

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a href="{{ route('admin.businesses.index') }}" class="text-sm font-bold text-[#0f5f59]">Volver a businesses</a>
        <a href="{{ route('admin.payments.index', ['q' => $business->slug]) }}" class="trebbia-button">Registrar pago</a>
    </div>

    @if (session('status'))
        <div class="mb-5 rounded-md border border-[#b9dfd5] bg-[#edf8f5] px-4 py-3 text-sm font-bold text-[#0f5f59]">
            {{ session('status') }}
        </div>
    @endif
    @include('partials.errors')

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <main class="space-y-6">
            <section class="trebbia-card p-6">
                <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#64748b]">Business</p>
                        <h2 class="mt-2 text-2xl font-bold">{{ $business->name }}</h2>
                        <p class="mt-1 text-sm text-[#64748b]">/{{ $business->slug }} - {{ $business->status }}</p>
                    </div>
                    <div class="rounded-md border border-[#d8e3dd] bg-[#f8fafc] px-4 py-3 text-sm">
                        <p class="font-bold">Owner</p>
                        <p class="text-[#64748b]">{{ $business->owner?->name ?: 'Sin nombre' }}</p>
                        <p class="text-[#64748b]">{{ $business->owner?->email }}</p>
                    </div>
                </div>

                <dl class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">Industria</dt>
                        <dd class="mt-1 font-bold">{{ $business->industry ?: 'Sin definir' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">Correo</dt>
                        <dd class="mt-1 break-words font-bold">{{ $business->email ?: 'Sin correo' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">Telefono</dt>
                        <dd class="mt-1 font-bold">{{ $business->phone ?: 'Sin telefono' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">Zona / moneda</dt>
                        <dd class="mt-1 font-bold">{{ $business->timezone }} / {{ $business->currency }}</dd>
                    </div>
                </dl>
            </section>

            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
                @foreach ($usage as $label => $value)
                    <div class="trebbia-card p-5">
                        <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">{{ $label }}</p>
                        <p class="mt-2 text-2xl font-bold">{{ $value }}</p>
                    </div>
                @endforeach
            </section>

            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <h2 class="text-lg font-bold">Pagos recientes</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[48rem] text-left text-sm">
                        <thead class="bg-[#f8fafc] text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">
                            <tr>
                                <th class="px-5 py-3">Fecha</th>
                                <th class="px-5 py-3">Plan</th>
                                <th class="px-5 py-3">Valor</th>
                                <th class="px-5 py-3">Metodo</th>
                                <th class="px-5 py-3">Registrado por</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e7ebe7]">
                            @forelse ($payments as $payment)
                                <tr>
                                    <td class="px-5 py-4">{{ $payment->paid_at?->format('d/m/Y H:i') }}</td>
                                    <td class="px-5 py-4">{{ $payment->plan?->name }}</td>
                                    <td class="px-5 py-4 font-bold">${{ number_format($payment->amount_cents / 100, 0, ',', '.') }} {{ $payment->currency }}</td>
                                    <td class="px-5 py-4">
                                        <p>{{ $payment->payment_method }}</p>
                                        <p class="text-[#64748b]">{{ $payment->reference ?: 'Sin referencia' }}</p>
                                    </td>
                                    <td class="px-5 py-4">{{ $payment->recordedBy?->email }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-8 text-center text-[#64748b]">Este negocio aun no tiene pagos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="grid gap-6 xl:grid-cols-2">
                <div class="trebbia-card overflow-hidden">
                    <div class="border-b border-[#e7ebe7] p-5">
                        <h2 class="text-lg font-bold">Historial de membresia</h2>
                    </div>
                    <div class="divide-y divide-[#e7ebe7]">
                        @forelse ($events as $event)
                            <div class="p-5 text-sm">
                                <p class="font-bold">{{ $event->from_status ?: 'nuevo' }} -> {{ $event->to_status ?: 'sin cambio' }}</p>
                                <p class="mt-1 text-[#64748b]">{{ $event->fromPlan?->name ?: 'Sin plan' }} -> {{ $event->toPlan?->name ?: 'Sin plan' }}</p>
                                <p class="mt-1 text-[#64748b]">{{ $event->reason }} - {{ $event->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        @empty
                            <p class="p-5 text-sm text-[#64748b]">Sin movimientos de membresia.</p>
                        @endforelse
                    </div>
                </div>

                <div class="trebbia-card overflow-hidden">
                    <div class="border-b border-[#e7ebe7] p-5">
                        <h2 class="text-lg font-bold">Auditoria TREBBIA ADMIN</h2>
                    </div>
                    <div class="divide-y divide-[#e7ebe7]">
                        @forelse ($auditLogs as $log)
                            <div class="p-5 text-sm">
                                <p class="font-bold">{{ $log->action }}</p>
                                <p class="mt-1 text-[#64748b]">{{ $log->user?->email }} - {{ $log->created_at->format('d/m/Y H:i') }}</p>
                                @if ($log->reason)
                                    <p class="mt-1 text-[#64748b]">Motivo: {{ $log->reason }}</p>
                                @endif
                            </div>
                        @empty
                            <p class="p-5 text-sm text-[#64748b]">Sin auditoria registrada para este negocio.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        </main>

        <aside class="space-y-6">
            <section class="trebbia-card p-6">
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#64748b]">Membresia</p>
                <h2 class="mt-2 text-xl font-bold">{{ $statusLabels[$subscription?->status] ?? 'Sin suscripcion' }}</h2>
                <p class="mt-1 text-sm text-[#64748b]">{{ $subscription?->plan?->name ?? 'Trial sin plan' }}</p>

                <dl class="mt-5 space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#64748b]">Trial termina</dt>
                        <dd class="font-bold">{{ $subscription?->trial_ends_at?->format('d/m/Y') ?? 'No aplica' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#64748b]">Dias trial</dt>
                        <dd class="font-bold">{{ $subscription ? $subscription->trialDaysRemaining() : 0 }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-[#64748b]">Periodo hasta</dt>
                        <dd class="font-bold">{{ $subscription?->current_period_ends_at?->format('d/m/Y') ?? 'Pendiente' }}</dd>
                    </div>
                </dl>
            </section>

            @if ($subscription)
                <section class="trebbia-card p-6">
                    <h2 class="text-lg font-bold">Accion rapida</h2>
                    <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}" class="mt-5 grid gap-4">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">
                        <div>
                            <label class="trebbia-label" for="status">Estado</label>
                            <select class="trebbia-input" id="status" name="status" required>
                                @foreach ($statusLabels as $value => $label)
                                    <option value="{{ $value }}" @selected($subscription->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="trebbia-label" for="plan_id">Plan</label>
                            <select class="trebbia-input" id="plan_id" name="plan_id">
                                <option value="">Sin cambiar plan</option>
                                @foreach ($plans as $plan)
                                    <option value="{{ $plan->id }}" @selected($subscription->plan_id === $plan->id)>{{ $plan->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="trebbia-label" for="reason">Motivo de auditoria</label>
                            <textarea class="trebbia-input min-h-24 resize-none" id="reason" name="reason" required placeholder="Ej: ajuste solicitado por pago confirmado."></textarea>
                        </div>
                        <button class="trebbia-button">Actualizar membresia</button>
                    </form>
                </section>
            @endif

            <section class="trebbia-card border-l-4 border-l-[#0f5f59] p-6">
                <h2 class="text-lg font-bold">Politica de soporte sensible</h2>
                <p class="mt-2 text-sm leading-6 text-[#64748b]">
                    Esta ficha no muestra historias clinicas, notas privadas de clientes ni contenido sensible del negocio.
                    Cualquier acceso de soporte a datos sensibles debe tener permiso especial, motivo registrado y auditoria.
                </p>
            </section>
        </aside>
    </div>
@endsection
