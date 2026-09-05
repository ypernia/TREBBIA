@extends('layouts.app')

@section('title', 'Dashboard | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Inicio')

@section('content')
    @php
        $statusTone = $todayStatus['state'] === 'attention'
            ? 'border-[#f4c7b8] bg-[#fff7ed] text-[#8a3027]'
            : 'border-[#b9dfd5] bg-[#edf8f5] text-[#0f5f59]';
        $statusIconTone = $todayStatus['state'] === 'attention'
            ? 'bg-[#ffe4d6] text-[#8a3027]'
            : 'bg-[#dff4ed] text-[#0f5f59]';
    @endphp

    <section class="trebbia-card mb-6 overflow-hidden border {{ $statusTone }}">
        <div class="grid gap-5 p-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div class="flex items-start gap-4">
                <span class="flex size-11 shrink-0 items-center justify-center rounded-md {{ $statusIconTone }}">
                    <x-icon name="calendar" class="size-5" />
                </span>
                <div>
                    <p class="text-sm font-bold uppercase tracking-[0.16em] opacity-80">Estado de hoy</p>
                    <h2 class="mt-1 text-2xl font-bold">{{ $todayStatus['title'] }}</h2>
                    <p class="mt-1 text-sm leading-6 opacity-90">{{ $todayStatus['message'] }}</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 text-center sm:min-w-[30rem] lg:grid-cols-5">
                <div class="rounded-md bg-white/70 p-3">
                    <p class="text-2xl font-bold">{{ $metrics['todayAppointments'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] opacity-70">Citas</p>
                </div>
                <div class="rounded-md bg-white/70 p-3">
                    <p class="text-2xl font-bold">{{ $metrics['pendingRequests'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] opacity-70">Solicitudes</p>
                </div>
                <div class="rounded-md bg-white/70 p-3">
                    <p class="text-2xl font-bold">{{ $metrics['todayProfessionals'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] opacity-70">Equipo</p>
                </div>
                <div class="rounded-md bg-white/70 p-3">
                    <p class="text-2xl font-bold">{{ $metrics['contactFollowUps'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] opacity-70">Contactar</p>
                </div>
                <div class="rounded-md bg-white/70 p-3">
                    <p class="text-2xl font-bold">{{ $metrics['todayBlockedTimes'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] opacity-70">Bloqueos</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => 'Citas de hoy', 'value' => $metrics['todayAppointments'], 'icon' => 'calendar'],
            ['label' => 'Confirmadas hoy', 'value' => $metrics['todayConfirmedAppointments'], 'icon' => 'calendar'],
            ['label' => 'Por confirmar hoy', 'value' => $metrics['todayPendingAppointments'], 'icon' => 'calendar'],
            ['label' => 'Solicitudes pendientes', 'value' => $metrics['pendingRequests'], 'icon' => 'calendar'],
            ['label' => 'Profesionales activos', 'value' => $metrics['professionals'], 'icon' => 'users'],
        ] as $metric)
            <div class="trebbia-card p-5">
                <div class="flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-[#64716d]">{{ $metric['label'] }}</p>
                    <span class="flex size-9 items-center justify-center rounded-md bg-[#edf7f4] text-[#245f57]">
                        <x-icon :name="$metric['icon']" class="size-4" />
                    </span>
                </div>
                <p class="mt-3 text-3xl font-bold">{{ $metric['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_24rem]">
        <section class="space-y-6">
            @if ($pendingBookingRequests->isNotEmpty())
                <div class="trebbia-card overflow-hidden border-l-4 border-l-[#245f57]">
                    <div class="border-b border-[#e7ebe7] p-5">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Requiere tu atencion</p>
                        <h2 class="mt-1 text-lg font-bold">{{ $pendingBookingRequests->count() }} solicitud{{ $pendingBookingRequests->count() === 1 ? '' : 'es' }} de reserva pendiente{{ $pendingBookingRequests->count() === 1 ? '' : 's' }}</h2>
                    </div>
                    <div class="divide-y divide-[#e7ebe7]">
                        @foreach ($pendingBookingRequests as $bookingRequest)
                            <div class="grid gap-4 p-5 lg:grid-cols-[1fr_auto] lg:items-center">
                                <div>
                                    <p class="font-bold">{{ $bookingRequest->starts_at->format('d/m/Y H:i') }} - {{ $bookingRequest->service?->name }}</p>
                                    <p class="mt-1 text-sm text-[#64716d]">{{ $bookingRequest->client?->name ?: 'Cliente sin asignar' }} / {{ $bookingRequest->professional?->name ?: 'Profesional sin asignar' }}</p>
                                </div>
                                <div class="flex flex-col gap-2 sm:flex-row">
                                    <form method="POST" action="{{ route('booking-requests.accept', $bookingRequest) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="trebbia-button w-full sm:w-auto">Aceptar</button>
                                    </form>
                                    <form method="POST" action="{{ route('booking-requests.reject', $bookingRequest) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="trebbia-button trebbia-button-secondary w-full sm:w-auto">Rechazar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($contactFollowUpAppointments->isNotEmpty())
                <div class="trebbia-card overflow-hidden border-l-4 border-l-[#8a3027]">
                    <div class="border-b border-[#e7ebe7] p-5">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Seguimiento</p>
                        <h2 class="mt-1 text-lg font-bold">{{ $contactFollowUpAppointments->count() }} cita{{ $contactFollowUpAppointments->count() === 1 ? '' : 's' }} pendiente{{ $contactFollowUpAppointments->count() === 1 ? '' : 's' }} por contacto</h2>
                    </div>
                    <div class="divide-y divide-[#e7ebe7]">
                        @foreach ($contactFollowUpAppointments as $appointment)
                            <a href="{{ route('agenda.edit', $appointment) }}" class="block p-5 hover:bg-[#f8faf8]">
                                <p class="font-bold">{{ $appointment->starts_at->format('d/m/Y H:i') }} - {{ $appointment->service?->name }}</p>
                                <p class="mt-1 text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }} / {{ $appointment->professional?->name ?: 'Profesional sin asignar' }}</p>
                                <span class="mt-3 inline-flex rounded-md bg-[#fff7ed] px-2 py-1 text-xs font-bold text-[#8a3027]">{{ $appointment->contactStatusLabel() }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($errors->has('booking_request'))
                <div class="rounded-md border border-[#f0c9c4] bg-[#fff4f2] px-4 py-3 text-sm font-semibold text-[#8a3027]">
                    {{ $errors->first('booking_request') }}
                    @if (session('booking_request_alternatives'))
                        <p class="mt-2 text-[#53615d]">Opciones disponibles: {{ implode(' / ', session('booking_request_alternatives')) }}</p>
                    @endif
                </div>
            @endif

            <div class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Compartir reservas</p>
                            <h2 class="mt-1 text-lg font-bold">Tu canal publico esta al {{ $share['percent'] }}%</h2>
                            <p class="mt-1 text-sm text-[#64716d]">Copia enlaces, QR y mensajes para empezar a recibir citas.</p>
                        </div>
                        <a class="trebbia-button" href="{{ route('sharing.index') }}">Abrir centro</a>
                    </div>
                    <div class="mt-5 h-2 overflow-hidden rounded-full bg-[#edf2ef]">
                        <div class="h-full rounded-full bg-[#245f57]" style="width: {{ $share['percent'] }}%"></div>
                    </div>
                </div>
                <div class="grid gap-3 p-5 md:grid-cols-2">
                    <div class="rounded-md border border-[#e1e6e0] bg-[#fbfcfb] p-4">
                        <p class="text-sm font-bold text-[#18211f]">Pagina publica</p>
                        <p class="mt-2 break-all text-sm font-semibold text-[#245f57]">{{ $share['public_url'] }}</p>
                    </div>
                    <div class="rounded-md border border-[#e1e6e0] bg-[#fbfcfb] p-4">
                        <p class="text-sm font-bold text-[#18211f]">WhatsApp</p>
                        <p class="mt-2 break-all text-sm font-semibold text-[#245f57]">{{ $share['whatsapp_url'] ?: 'Configura el numero para generar enlace.' }}</p>
                    </div>
                </div>
            </div>

            <div class="trebbia-card p-5">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-lg font-bold">Agenda de hoy</h2>
                        <p class="mt-1 text-sm text-[#64716d]">Lo que el equipo debe atender durante el dia.</p>
                    </div>
                    <a href="{{ route('agenda.index') }}" class="trebbia-button trebbia-button-secondary">Ver agenda</a>
                </div>

                @if ($todayAppointments->isEmpty())
                    <x-empty-state
                        class="mt-6 rounded-md border border-dashed border-[#cfd8d2] bg-[#f8faf8]"
                        icon="calendar"
                        title="Hoy no hay citas programadas"
                        body="Comparte tu pagina de reservas o crea una cita manual para empezar."
                        :action="route('agenda.create')"
                        action-label="Crear cita"
                    />
                @else
                    <div class="mt-5 space-y-3">
                        @foreach ($todayAppointments as $appointment)
                            <a href="{{ route('agenda.edit', $appointment) }}" class="block rounded-md border border-[#e1e6e0] p-4 hover:border-[#b9d8cd]">
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                                    <div>
                                        <p class="font-bold">{{ $appointment->starts_at->format('H:i') }} - {{ $appointment->service?->name }}</p>
                                        <p class="text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }} / {{ $appointment->professional?->name ?: 'Profesional sin asignar' }}</p>
                                    </div>
                                    <span class="w-fit rounded-md bg-[#edf7f4] px-2 py-1 text-xs font-bold text-[#245f57]">{{ ucfirst($appointment->status) }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            @if ($todayBlockedTimes->isNotEmpty())
                <div class="trebbia-card overflow-hidden">
                    <div class="border-b border-[#e7ebe7] p-5">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Disponibilidad</p>
                        <h2 class="mt-1 text-lg font-bold">Bloqueos de hoy</h2>
                    </div>
                    <div class="divide-y divide-[#e7ebe7]">
                        @foreach ($todayBlockedTimes as $blockedTime)
                            <div class="p-5">
                                <p class="font-bold">{{ $blockedTime->starts_at->format('H:i') }} - {{ $blockedTime->ends_at->format('H:i') }}</p>
                                <p class="mt-1 text-sm text-[#64716d]">{{ $blockedTime->reason ?: 'Bloqueo de agenda' }}</p>
                                <p class="text-sm text-[#64716d]">
                                    @if ($blockedTime->professional)
                                        Profesional: {{ $blockedTime->professional->name }}
                                    @elseif ($blockedTime->resource)
                                        Recurso: {{ $blockedTime->resource->name }}
                                    @else
                                        Agenda general
                                    @endif
                                </p>
                                <a class="mt-3 inline-flex text-sm font-bold text-[#245f57] hover:underline" href="{{ route('blocked-times.show', $blockedTime) }}">Resolver citas</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </section>

        <aside class="space-y-6">
            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <h2 class="text-lg font-bold">Checklist de activacion</h2>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $share['completed'] }} de {{ $share['total'] }} pasos listos.</p>
                </div>
                <div class="divide-y divide-[#e7ebe7]">
                    @foreach ($share['checklist'] as $item)
                        <a class="block p-4 hover:bg-[#f8faf8]" href="{{ $item['action'] }}">
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-3 w-3 rounded-full {{ $item['complete'] ? 'bg-[#245f57]' : 'bg-[#cfd8d2]' }}"></span>
                                <div>
                                    <p class="text-sm font-bold">{{ $item['label'] }}</p>
                                    <p class="mt-1 text-xs text-[#64716d]">{{ $item['description'] }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Base multiempresa</h2>
                <div class="mt-4 space-y-3 text-sm text-[#53615d]">
                    <p><span class="font-bold text-[#18211f]">Tenant:</span> {{ $business->name }}</p>
                    <p><span class="font-bold text-[#18211f]">Estado:</span> {{ $business->status }}</p>
                    <p><span class="font-bold text-[#18211f]">Zona horaria:</span> {{ $business->timezone }}</p>
                </div>
            </section>
        </aside>
    </div>
@endsection
