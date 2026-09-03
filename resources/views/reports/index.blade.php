@extends('layouts.app')

@section('title', 'Reportes | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Reportes')

@section('content')
    @php
        $money = fn (int $cents): string => '$'.number_format($cents / 100, 0, ',', '.');
        $maxStatus = max(1, collect($appointmentsByStatus)->max('count'));
    @endphp

    <div class="mb-5 grid gap-3 xl:grid-cols-[1fr_auto] xl:items-end">
        <form method="GET" action="{{ route('reports.index') }}" class="trebbia-card grid gap-3 p-4 sm:grid-cols-[11rem_11rem_auto] sm:items-end">
            <div>
                <label class="trebbia-label" for="start_date">Desde</label>
                <input class="trebbia-input" id="start_date" type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}">
            </div>
            <div>
                <label class="trebbia-label" for="end_date">Hasta</label>
                <input class="trebbia-input" id="end_date" type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}">
            </div>
            <button class="trebbia-button trebbia-button-secondary">Filtrar</button>
        </form>
        <a class="trebbia-button" href="{{ route('agenda.index', ['view' => 'week']) }}">Ver agenda</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['label' => 'Citas', 'value' => $metrics['appointments']],
            ['label' => 'Completadas', 'value' => $metrics['completed']],
            ['label' => 'Canceladas', 'value' => $metrics['cancelled']],
            ['label' => 'Ingresos estimados', 'value' => $money($metrics['estimatedRevenueCents'])],
            ['label' => 'Ticket promedio', 'value' => $money($metrics['averageTicketCents'])],
        ] as $metric)
            <div class="trebbia-card p-5">
                <p class="text-sm font-semibold text-[#64716d]">{{ $metric['label'] }}</p>
                <p class="mt-3 text-3xl font-bold">{{ $metric['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_24rem]">
        <main class="space-y-6">
            <section class="trebbia-card p-5">
                <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-lg font-bold">Citas por estado</h2>
                        <p class="mt-1 text-sm text-[#64716d]">{{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</p>
                    </div>
                    <p class="text-sm font-bold text-[#245f57]">{{ $metrics['newClients'] }} cliente{{ $metrics['newClients'] === 1 ? '' : 's' }} nuevo{{ $metrics['newClients'] === 1 ? '' : 's' }}</p>
                </div>

                <div class="mt-5 space-y-4">
                    @foreach ($appointmentsByStatus as $status)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-3 text-sm">
                                <p class="font-bold">{{ $status['label'] }}</p>
                                <p class="text-[#64716d]">{{ $status['count'] }}</p>
                            </div>
                            <div class="h-3 overflow-hidden rounded-full bg-[#edf2ef]">
                                <div class="h-full rounded-full bg-[#245f57]" style="width: {{ ($status['count'] / $maxStatus) * 100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="trebbia-card overflow-hidden">
                    <div class="border-b border-[#e7ebe7] p-5">
                        <h2 class="text-lg font-bold">Servicios mas reservados</h2>
                    </div>
                    @forelse ($topServices as $service)
                        <div class="grid gap-3 border-b border-[#e7ebe7] p-5 sm:grid-cols-[1fr_5rem_7rem] sm:items-center">
                            <p class="font-bold">{{ $service['name'] }}</p>
                            <p class="text-sm text-[#64716d]">{{ $service['count'] }} cita{{ $service['count'] === 1 ? '' : 's' }}</p>
                            <p class="text-sm font-bold">{{ $money($service['revenue_cents']) }}</p>
                        </div>
                    @empty
                        <p class="p-5 text-sm text-[#64716d]">Sin servicios en el rango.</p>
                    @endforelse
                </div>

                <div class="trebbia-card overflow-hidden">
                    <div class="border-b border-[#e7ebe7] p-5">
                        <h2 class="text-lg font-bold">Carga por profesional</h2>
                    </div>
                    @forelse ($topProfessionals as $professional)
                        <div class="grid gap-3 border-b border-[#e7ebe7] p-5 sm:grid-cols-[1fr_5rem_6rem] sm:items-center">
                            <p class="font-bold">{{ $professional['name'] }}</p>
                            <p class="text-sm text-[#64716d]">{{ $professional['count'] }} cita{{ $professional['count'] === 1 ? '' : 's' }}</p>
                            <p class="text-sm font-bold">{{ $professional['completed'] }} hechas</p>
                        </div>
                    @empty
                        <p class="p-5 text-sm text-[#64716d]">Sin profesionales en el rango.</p>
                    @endforelse
                </div>
            </section>
        </main>

        <aside class="space-y-6">
            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Proximas citas</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($upcomingAppointments as $appointment)
                        <a href="{{ route('agenda.edit', $appointment) }}" class="block rounded-md border border-[#e1e6e0] p-4 hover:border-[#b9d8cd]">
                            <p class="font-bold">{{ $appointment->starts_at->format('d/m H:i') }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }}</p>
                            <p class="text-sm text-[#64716d]">{{ $appointment->service?->name ?: 'Servicio sin asignar' }}</p>
                        </a>
                    @empty
                        <p class="rounded-md border border-dashed border-[#cfd8d2] p-4 text-sm text-[#64716d]">Sin proximas citas.</p>
                    @endforelse
                </div>
            </section>

            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Clientes recientes</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($recentClients as $client)
                        <a href="{{ route('clientes.show', $client) }}" class="block rounded-md border border-[#e1e6e0] p-4 hover:border-[#b9d8cd]">
                            <p class="font-bold">{{ $client->name }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">{{ $client->created_at->format('d/m/Y') }}</p>
                        </a>
                    @empty
                        <p class="rounded-md border border-dashed border-[#cfd8d2] p-4 text-sm text-[#64716d]">Sin clientes registrados.</p>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
@endsection
