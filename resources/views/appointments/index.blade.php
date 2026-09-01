@extends('layouts.app')

@section('title', 'Agenda | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Agenda')

@section('content')
    <div class="mb-5 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
        <form method="GET" action="{{ route('agenda.index') }}" class="flex max-w-md gap-2">
            <input class="trebbia-input" type="date" name="date" value="{{ $date->format('Y-m-d') }}">
            <button class="trebbia-button trebbia-button-secondary">Ver dia</button>
        </form>
        <a class="trebbia-button" href="{{ route('agenda.create') }}">Nueva cita</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <section class="trebbia-card overflow-hidden">
            <div class="border-b border-[#e7ebe7] p-5">
                <h2 class="text-lg font-bold">{{ $date->translatedFormat('l d/m/Y') }}</h2>
                <p class="mt-1 text-sm text-[#64716d]">Citas programadas para el negocio activo.</p>
            </div>
            @forelse ($appointments as $appointment)
                <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[7rem_1fr_9rem_10rem] md:items-center">
                    <div>
                        <p class="text-xl font-bold">{{ $appointment->starts_at->format('H:i') }}</p>
                        <p class="text-sm text-[#64716d]">{{ $appointment->ends_at->format('H:i') }}</p>
                    </div>
                    <div>
                        <p class="font-bold">{{ $appointment->service?->name ?: 'Servicio sin asignar' }}</p>
                        <p class="mt-1 text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }} · {{ $appointment->professional?->name ?: 'Profesional sin asignar' }}</p>
                        @if ($appointment->resource)
                            <p class="mt-1 text-sm text-[#64716d]">Recurso: {{ $appointment->resource->name }}</p>
                        @endif
                    </div>
                    <span class="w-fit rounded-md bg-[#edf7f4] px-2 py-1 text-xs font-bold text-[#245f57]">{{ ucfirst($appointment->status) }}</span>
                    <div class="flex items-center gap-2 md:justify-end">
                        <a class="rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('agenda.edit', $appointment) }}">Editar</a>
                        <form method="POST" action="{{ route('agenda.destroy', $appointment) }}">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-md border border-[#f0c9c4] px-3 py-2 text-sm font-bold text-[#8a3027]">Archivar</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="p-8 text-center">
                    <p class="font-bold">No hay citas en este dia</p>
                    <p class="mx-auto mt-2 max-w-md text-sm text-[#64716d]">Crea una cita usando un servicio y un profesional activo. TREBBIA validara el horario general y traslapes simples.</p>
                    <a class="trebbia-button mt-5" href="{{ route('agenda.create') }}">Crear cita</a>
                </div>
            @endforelse
        </section>

        <aside class="trebbia-card p-5">
            <h2 class="text-lg font-bold">Proximas</h2>
            <div class="mt-4 space-y-3">
                @forelse ($upcoming as $appointment)
                    <div class="rounded-md border border-[#e1e6e0] p-4">
                        <p class="font-bold">{{ $appointment->starts_at->format('d/m H:i') }}</p>
                        <p class="mt-1 text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }}</p>
                        <p class="text-sm text-[#64716d]">{{ $appointment->service?->name }}</p>
                    </div>
                @empty
                    <p class="rounded-md border border-dashed border-[#cfd8d2] p-4 text-sm text-[#64716d]">Sin proximas citas.</p>
                @endforelse
            </div>
        </aside>
    </div>
@endsection
