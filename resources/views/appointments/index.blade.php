@extends('layouts.app')

@section('title', 'Agenda | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Agenda')

@section('content')
    @php
        $statusStyles = [
            'scheduled' => 'bg-[#eef2ff] text-[#39447a]',
            'confirmed' => 'bg-[#edf7f4] text-[#245f57]',
            'cancelled' => 'bg-[#fff4f2] text-[#8a3027]',
            'completed' => 'bg-[#f1f1ef] text-[#53615d]',
        ];
        $sourceStyles = [
            'internal' => 'bg-[#f1f5f9] text-[#334155]',
            'public_booking' => 'bg-[#ecfeff] text-[#155e75]',
            'whatsapp' => 'bg-[#edf7f4] text-[#245f57]',
        ];
    @endphp

    <div class="mb-5 flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
        <form method="GET" action="{{ route('agenda.index') }}" class="trebbia-card grid gap-3 p-4 md:grid-cols-[9rem_10rem_1fr_1fr_10rem_auto] md:items-end">
            <div>
                <label class="trebbia-label" for="view">Vista</label>
                <select class="trebbia-input" id="view" name="view">
                    <option value="day" @selected($view === 'day')>Dia</option>
                    <option value="week" @selected($view === 'week')>Semana</option>
                </select>
            </div>
            <div>
                <label class="trebbia-label" for="date">Fecha</label>
                <input class="trebbia-input" id="date" type="date" name="date" value="{{ $date->format('Y-m-d') }}">
            </div>
            <div>
                <label class="trebbia-label" for="professional_id">Profesional</label>
                <select class="trebbia-input" id="professional_id" name="professional_id">
                    <option value="">Todos</option>
                    @foreach ($professionals as $professional)
                        <option value="{{ $professional->id }}" @selected((string) $filters['professional_id'] === (string) $professional->id)>{{ $professional->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="trebbia-label" for="service_id">Servicio</label>
                <select class="trebbia-input" id="service_id" name="service_id">
                    <option value="">Todos</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected((string) $filters['service_id'] === (string) $service->id)>{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="trebbia-label" for="status">Estado</label>
                <select class="trebbia-input" id="status" name="status">
                    <option value="">Todos</option>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="trebbia-button trebbia-button-secondary">Filtrar</button>
        </form>
        <a class="trebbia-button" href="{{ route('agenda.create', ['date' => $date->format('Y-m-d')]) }}">Nueva cita</a>
    </div>

    <div class="mb-5 flex flex-wrap gap-2">
        <a class="rounded-md border border-[#d7ddd7] bg-white px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('agenda.index', ['view' => $view, 'date' => $date->subDay()->format('Y-m-d')] + array_filter($filters)) }}">Anterior</a>
        <a class="rounded-md border border-[#d7ddd7] bg-white px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('agenda.index', ['view' => $view, 'date' => today()->format('Y-m-d')] + array_filter($filters)) }}">Hoy</a>
        <a class="rounded-md border border-[#d7ddd7] bg-white px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('agenda.index', ['view' => $view, 'date' => $date->addDay()->format('Y-m-d')] + array_filter($filters)) }}">Siguiente</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <section class="trebbia-card overflow-hidden">
            <div class="border-b border-[#e7ebe7] p-5">
                <h2 class="text-lg font-bold">{{ $view === 'week' ? 'Semana del '.$weekStart->format('d/m/Y') : $date->isoFormat('dddd DD/MM/YYYY') }}</h2>
                <p class="mt-1 text-sm text-[#64716d]">{{ $appointments->count() }} cita{{ $appointments->count() === 1 ? '' : 's' }} en la vista actual.</p>
            </div>

            @if ($view === 'week')
                <div class="grid divide-y divide-[#e7ebe7] lg:grid-cols-7 lg:divide-x lg:divide-y-0">
                    @foreach ($weekDays as $day)
                        @php $dayAppointments = $appointmentsByDay->get($day->toDateString(), collect()); @endphp
                        <div class="min-h-48 p-4">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="text-sm font-bold text-[#245f57]">{{ $day->isoFormat('ddd') }}</p>
                                    <p class="text-xl font-bold">{{ $day->format('d') }}</p>
                                </div>
                                <a class="rounded-md border border-[#d7ddd7] px-2 py-1 text-xs font-bold text-[#245f57]" href="{{ route('agenda.create', ['date' => $day->format('Y-m-d'), 'professional_id' => $filters['professional_id'], 'service_id' => $filters['service_id']]) }}">Crear</a>
                            </div>
                            <div class="mt-4 space-y-2">
                                @forelse ($dayAppointments as $appointment)
                                    <a href="{{ route('agenda.edit', $appointment) }}" class="block rounded-md border border-[#e1e6e0] bg-white p-3 hover:border-[#b9d8cd]">
                                        <p class="text-sm font-bold">{{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}</p>
                                        <p class="mt-1 text-sm text-[#53615d]">{{ $appointment->service?->name ?: 'Servicio' }}</p>
                                        <p class="text-xs text-[#64716d]">{{ $appointment->professional?->name ?: 'Profesional' }}</p>
                                        <p class="text-xs text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }}</p>
                                        <span class="mt-2 inline-flex w-fit rounded-md px-2 py-1 text-[11px] font-bold {{ $sourceStyles[$appointment->source_channel] ?? 'bg-[#f1f1ef] text-[#53615d]' }}">{{ $appointment->sourceLabel() }}</span>
                                    </a>
                                @empty
                                    <p class="rounded-md border border-dashed border-[#cfd8d2] p-3 text-sm text-[#64716d]">Sin citas</p>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                @forelse ($appointments as $appointment)
                    <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[7rem_1fr_9rem_10rem] md:items-center">
                        <div>
                            <p class="text-xl font-bold">{{ $appointment->starts_at->format('H:i') }}</p>
                            <p class="text-sm text-[#64716d]">{{ $appointment->ends_at->format('H:i') }}</p>
                        </div>
                        <div>
                            <p class="font-bold">{{ $appointment->service?->name ?: 'Servicio sin asignar' }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">
                                @if ($appointment->client)
                                    <a class="font-semibold text-[#245f57] hover:underline" href="{{ route('clientes.show', $appointment->client) }}">{{ $appointment->client->name }}</a>
                                @else
                                    Cliente sin asignar
                                @endif
                                - {{ $appointment->professional?->name ?: 'Profesional sin asignar' }}
                            </p>
                            @if ($appointment->resource)
                                <p class="mt-1 text-sm text-[#64716d]">Recurso: {{ $appointment->resource->name }}</p>
                            @endif
                        </div>
                        <span class="w-fit rounded-md px-2 py-1 text-xs font-bold {{ $statusStyles[$appointment->status] ?? 'bg-[#f1f1ef] text-[#53615d]' }}">{{ $statuses[$appointment->status] ?? ucfirst($appointment->status) }}</span>
                        <div class="flex items-center gap-2 md:justify-end">
                            <span class="hidden rounded-md px-2 py-1 text-xs font-bold md:inline-flex {{ $sourceStyles[$appointment->source_channel] ?? 'bg-[#f1f1ef] text-[#53615d]' }}">{{ $appointment->sourceLabel() }}</span>
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
                        <p class="mx-auto mt-2 max-w-md text-sm text-[#64716d]">Crea una cita usando un servicio y un profesional activo. TREBBIA validara horario y traslapes antes de guardar.</p>
                        <a class="trebbia-button mt-5" href="{{ route('agenda.create', ['date' => $date->format('Y-m-d')]) }}">Crear cita</a>
                    </div>
                @endforelse
            @endif
        </section>

        <aside class="trebbia-card p-5">
            <h2 class="text-lg font-bold">Proximas</h2>
            <div class="mt-4 space-y-3">
                @forelse ($upcoming as $appointment)
                    <a href="{{ route('agenda.edit', $appointment) }}" class="block rounded-md border border-[#e1e6e0] p-4 hover:border-[#b9d8cd]">
                        <p class="font-bold">{{ $appointment->starts_at->format('d/m H:i') }}</p>
                        <p class="mt-1 text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }}</p>
                        <p class="text-sm text-[#64716d]">{{ $appointment->service?->name }}</p>
                        <span class="mt-3 inline-flex rounded-md px-2 py-1 text-xs font-bold {{ $sourceStyles[$appointment->source_channel] ?? 'bg-[#f1f1ef] text-[#53615d]' }}">{{ $appointment->sourceLabel() }}</span>
                    </a>
                @empty
                    <p class="rounded-md border border-dashed border-[#cfd8d2] p-4 text-sm text-[#64716d]">Sin proximas citas.</p>
                @endforelse
            </div>
        </aside>
    </div>
@endsection
