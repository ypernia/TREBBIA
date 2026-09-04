@extends('layouts.app')

@section('title', 'Dashboard | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Inicio')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => 'Citas de hoy', 'value' => $metrics['todayAppointments']],
            ['label' => 'Proximas citas', 'value' => $metrics['upcomingAppointments']],
            ['label' => 'Clientes', 'value' => $metrics['clients']],
            ['label' => 'Profesionales activos', 'value' => $metrics['professionals']],
            ['label' => 'Servicios activos', 'value' => $metrics['services']],
        ] as $metric)
            <div class="trebbia-card p-5">
                <p class="text-sm font-semibold text-[#64716d]">{{ $metric['label'] }}</p>
                <p class="mt-3 text-3xl font-bold">{{ $metric['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_24rem]">
        <section class="space-y-6">
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
                        <h2 class="text-lg font-bold">Proximas citas</h2>
                        <p class="mt-1 text-sm text-[#64716d]">Aqui aparece la agenda operativa de reservas confirmadas o pendientes.</p>
                    </div>
                    <a href="{{ route('agenda.index') }}" class="trebbia-button trebbia-button-secondary">Ver agenda</a>
                </div>

                @if ($upcomingAppointments->isEmpty())
                    <div class="mt-6 rounded-md border border-dashed border-[#cfd8d2] bg-[#f8faf8] p-8 text-center">
                        <p class="font-bold">Aun no hay citas programadas</p>
                        <p class="mx-auto mt-2 max-w-md text-sm text-[#64716d]">Comparte tu pagina de reservas o crea una cita manual para empezar a llenar la agenda.</p>
                    </div>
                @else
                    <div class="mt-5 space-y-3">
                        @foreach ($upcomingAppointments as $appointment)
                            <div class="rounded-md border border-[#e1e6e0] p-4">
                                <p class="font-bold">{{ $appointment->starts_at->format('d/m/Y H:i') }}</p>
                                <p class="text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }} / {{ ucfirst($appointment->status) }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
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
