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

    <div class="mb-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-[1fr_12rem_12rem_auto] xl:items-center">
        <section class="trebbia-card p-5 sm:col-span-2 xl:col-span-1">
            <p class="text-sm font-semibold text-[#64716d]">Modulo clinico</p>
            <h2 class="mt-1 text-xl font-bold">Evoluciones y registros por paciente</h2>
            <p class="mt-2 text-sm text-[#64716d]">Centraliza motivo de consulta, impresion diagnostica, escala de dolor, plan de tratamiento, evolucion y recomendaciones.</p>
        </section>
        <div class="trebbia-card p-5">
            <p class="text-sm font-semibold text-[#64716d]">Registros</p>
            <p class="mt-2 text-3xl font-bold">{{ $recordsCount }}</p>
        </div>
        <div class="trebbia-card p-5">
            <p class="text-sm font-semibold text-[#64716d]">Pacientes</p>
            <p class="mt-2 text-3xl font-bold">{{ $clientsCount }}</p>
        </div>
        <a class="trebbia-button" href="{{ route('clientes.index') }}">Abrir clientes</a>
    </div>

    <div class="trebbia-card overflow-hidden">
        <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[9rem_1fr_12rem_9rem] md:items-center">
            <p class="text-sm font-bold text-[#33413d]">Fecha</p>
            <p class="text-sm font-bold text-[#33413d]">Paciente y detalle</p>
            <p class="text-sm font-bold text-[#33413d]">Profesional</p>
            <p class="text-sm font-bold text-[#33413d]">Estado</p>
        </div>
        @forelse ($records as $record)
            <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[9rem_1fr_12rem_9rem] md:items-center">
                <div>
                    <p class="font-bold">{{ $record->record_date->format('d/m/Y') }}</p>
                    @if (! is_null($record->pain_scale))
                        <p class="mt-1 text-sm text-[#64716d]">Dolor {{ $record->pain_scale }}/10</p>
                    @endif
                </div>
                <div>
                    <a class="font-bold text-[#245f57] hover:underline" href="{{ route('clientes.show', $record->client) }}">{{ $record->client?->name ?: 'Paciente sin asignar' }}</a>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $record->reason_for_visit ?: 'Sin motivo registrado' }}</p>
                    @if ($record->diagnosis)
                        <p class="mt-1 text-sm text-[#64716d]">{{ str($record->diagnosis)->limit(110) }}</p>
                    @endif
                </div>
                <p class="text-sm text-[#64716d]">{{ $record->professional?->name ?: 'Sin asignar' }}</p>
                <span class="w-fit rounded-md px-2 py-1 text-xs font-bold {{ $statusStyles[$record->status] ?? 'bg-[#f1f1ef] text-[#53615d]' }}">
                    {{ $statusLabels[$record->status] ?? ucfirst($record->status) }}
                </span>
            </div>
        @empty
            <div class="p-8 text-center">
                <p class="font-bold">Sin historias clinicas todavia</p>
                <p class="mt-2 text-sm text-[#64716d]">Abre un paciente desde Clientes y registra su primera evolucion.</p>
                <a class="trebbia-button mt-5" href="{{ route('clientes.index') }}">Ir a clientes</a>
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $records->links() }}</div>
@endsection
