@extends('layouts.app')

@section('title', 'Historia clinica | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Historia clinica')

@section('content')
    @php
        $statusLabels = \App\Models\ClinicalRecord::statusLabels();
        $statusStyles = [
            'draft' => 'bg-[#fff7ed] text-[#9a4f12]',
            'final' => 'bg-[#edf7f4] text-[#245f57]',
        ];
    @endphp

    <div class="mb-5 grid gap-4 xl:grid-cols-[1fr_14rem_14rem] xl:items-stretch">
        <section class="trebbia-card p-5">
            <p class="text-sm font-semibold text-[#64716d]">Modulo clinico</p>
            <h2 class="mt-1 text-xl font-bold">Selecciona un paciente y alimenta su historia</h2>
            <p class="mt-2 max-w-2xl text-sm text-[#64716d]">La historia clinica vive dentro de la ficha del paciente. Desde aqui puedes buscarlo, abrirlo y registrar una nueva valoracion o evolucion.</p>
        </section>
        <div class="trebbia-card p-5">
            <p class="text-sm font-semibold text-[#64716d]">Registros</p>
            <p class="mt-2 text-3xl font-bold">{{ $recordsCount }}</p>
        </div>
        <div class="trebbia-card p-5">
            <p class="text-sm font-semibold text-[#64716d]">Pacientes</p>
            <p class="mt-2 text-3xl font-bold">{{ $clientsCount }}</p>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[23rem_1fr]">
        <aside class="space-y-6">
            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Buscar paciente</h2>
                <form method="GET" action="{{ route('clinical-records.index') }}" class="mt-4 grid gap-3">
                    <div>
                        <label class="trebbia-label" for="q">Nombre, telefono o documento</label>
                        <input class="trebbia-input" id="q" name="q" value="{{ $search }}" placeholder="Ej: Ana Ruiz">
                    </div>
                    <button class="trebbia-button trebbia-button-secondary">Buscar</button>
                </form>
                <a class="trebbia-button mt-3 w-full" href="{{ route('clientes.create') }}">
                    <x-icon name="plus" class="size-4" />
                    Nuevo paciente
                </a>
            </section>

            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <h2 class="text-lg font-bold">Pacientes activos</h2>
                    <p class="mt-1 text-sm text-[#64716d]">Abre uno para registrar la historia.</p>
                </div>
                @forelse ($clients as $client)
                    <a class="block border-b border-[#e7ebe7] p-4 hover:bg-[#f8faf8]" href="{{ route('clientes.show', $client) }}#historia-clinica">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-[#245f57]">{{ $client->name }}</p>
                                <p class="mt-1 text-sm text-[#64716d]">{{ $client->phone ?: $client->email ?: 'Sin contacto' }}</p>
                            </div>
                            <span class="rounded-md bg-[#edf7f4] px-2 py-1 text-xs font-bold text-[#245f57]">{{ $client->clinical_records_count }}</span>
                        </div>
                        <p class="mt-2 text-xs font-semibold text-[#64716d]">{{ $client->appointments_count }} cita{{ $client->appointments_count === 1 ? '' : 's' }}</p>
                    </a>
                @empty
                    <x-empty-state
                        icon="users"
                        title="No hay pacientes para mostrar"
                        body="Crea un paciente o ajusta la busqueda para alimentar su historia clinica."
                        :action="route('clientes.create')"
                        action-label="Crear paciente"
                    />
                @endforelse
            </section>
        </aside>

        <main class="space-y-6">
            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold">Ultimos registros</h2>
                            <p class="mt-1 text-sm text-[#64716d]">Consulta la actividad clinica reciente y abre el paciente para continuar.</p>
                        </div>
                        <a class="trebbia-button trebbia-button-secondary" href="{{ route('clientes.index') }}">Ver clientes</a>
                    </div>
                </div>
                <div class="hidden border-b border-[#e7ebe7] p-5 md:grid md:grid-cols-[9rem_1fr_12rem_9rem] md:items-center md:gap-3">
                    <p class="text-sm font-bold text-[#33413d]">Fecha</p>
                    <p class="text-sm font-bold text-[#33413d]">Paciente y detalle</p>
                    <p class="text-sm font-bold text-[#33413d]">Profesional</p>
                    <p class="text-sm font-bold text-[#33413d]">Estado</p>
                </div>
                @forelse ($records as $record)
                    <a class="grid gap-3 border-b border-[#e7ebe7] p-5 hover:bg-[#f8faf8] md:grid-cols-[9rem_1fr_12rem_9rem] md:items-center" href="{{ route('clientes.show', $record->client) }}#historia-clinica">
                        <div>
                            <p class="font-bold">{{ $record->record_date->format('d/m/Y') }}</p>
                            @if (! is_null($record->pain_scale))
                                <p class="mt-1 text-sm text-[#64716d]">Dolor {{ $record->pain_scale }}/10</p>
                            @endif
                        </div>
                        <div>
                            <p class="font-bold text-[#245f57]">{{ $record->client?->name ?: 'Paciente sin asignar' }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">{{ $record->reason_for_visit ?: 'Sin motivo registrado' }}</p>
                            @if ($record->diagnosis)
                                <p class="mt-1 text-sm text-[#64716d]">{{ str($record->diagnosis)->limit(110) }}</p>
                            @endif
                        </div>
                        <p class="text-sm text-[#64716d]">{{ $record->professional?->name ?: 'Sin asignar' }}</p>
                        <span class="w-fit rounded-md px-2 py-1 text-xs font-bold {{ $statusStyles[$record->status] ?? 'bg-[#f1f1ef] text-[#53615d]' }}">
                            {{ $statusLabels[$record->status] ?? ucfirst($record->status) }}
                        </span>
                    </a>
                @empty
                    <x-empty-state
                        icon="heart"
                        title="Sin historias clinicas todavia"
                        body="Selecciona un paciente activo para registrar su primera valoracion o evolucion."
                    />
                @endforelse
            </section>

            <div>{{ $records->links() }}</div>
        </main>
    </div>
@endsection
