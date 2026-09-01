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
        <section class="trebbia-card p-5">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold">Proximas citas</h2>
                    <p class="mt-1 text-sm text-[#64716d]">Aqui aparecera la agenda operativa cuando creemos citas.</p>
                </div>
                <a href="{{ route('agenda.index') }}" class="trebbia-button trebbia-button-secondary">Ver agenda</a>
            </div>

            @if ($upcomingAppointments->isEmpty())
                <div class="mt-6 rounded-md border border-dashed border-[#cfd8d2] bg-[#f8faf8] p-8 text-center">
                    <p class="font-bold">Aun no hay citas programadas</p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-[#64716d]">La base ya esta preparada. En la siguiente fase conectaremos servicios, clientes, profesionales y disponibilidad.</p>
                </div>
            @else
                <div class="mt-5 space-y-3">
                    @foreach ($upcomingAppointments as $appointment)
                        <div class="rounded-md border border-[#e1e6e0] p-4">
                            <p class="font-bold">{{ $appointment->starts_at->format('d/m/Y H:i') }}</p>
                            <p class="text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }} · {{ ucfirst($appointment->status) }}</p>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <aside class="trebbia-card p-5">
            <h2 class="text-lg font-bold">Base multiempresa</h2>
            <div class="mt-4 space-y-3 text-sm text-[#53615d]">
                <p><span class="font-bold text-[#18211f]">Tenant:</span> {{ $business->name }}</p>
                <p><span class="font-bold text-[#18211f]">Estado:</span> {{ $business->status }}</p>
                <p><span class="font-bold text-[#18211f]">Zona horaria:</span> {{ $business->timezone }}</p>
                <p><span class="font-bold text-[#18211f]">Regla:</span> las entidades operativas nacen con `business_id`.</p>
            </div>
        </aside>
    </div>
@endsection
