@extends('layouts.app')

@section('title', $client->name.' | TREBBIA')
@section('eyebrow', 'Cliente')
@section('page-title', $client->name)

@section('content')
    @php
        $statusLabels = [
            'scheduled' => 'Programada',
            'confirmed' => 'Confirmada',
            'cancelled' => 'Cancelada',
            'completed' => 'Completada',
        ];
        $statusStyles = [
            'scheduled' => 'bg-[#eef2ff] text-[#39447a]',
            'confirmed' => 'bg-[#edf7f4] text-[#245f57]',
            'cancelled' => 'bg-[#fff4f2] text-[#8a3027]',
            'completed' => 'bg-[#f1f1ef] text-[#53615d]',
        ];
    @endphp

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <a class="rounded-md border border-[#d7ddd7] bg-white px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('clientes.index') }}">Volver a clientes</a>
        <div class="flex flex-wrap gap-2">
            <a class="trebbia-button trebbia-button-secondary" href="{{ route('clientes.edit', $client) }}">Editar cliente</a>
            <a class="trebbia-button" href="{{ route('agenda.create', ['client_id' => $client->id]) }}">Agendar cita</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[22rem_1fr]">
        <aside class="space-y-6">
            <section class="trebbia-card p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">Ficha</h2>
                        <p class="mt-1 text-sm text-[#64716d]">{{ $business->name }}</p>
                    </div>
                    <span class="rounded-md px-2 py-1 text-xs font-bold {{ $client->is_active ? 'bg-[#edf7f4] text-[#245f57]' : 'bg-[#f1f1ef] text-[#53615d]' }}">
                        {{ $client->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>

                <dl class="mt-5 space-y-4 text-sm">
                    <div>
                        <dt class="font-bold text-[#33413d]">Correo</dt>
                        <dd class="mt-1 text-[#64716d]">{{ $client->email ?: 'Sin correo' }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-[#33413d]">Telefono</dt>
                        <dd class="mt-1 text-[#64716d]">{{ $client->phone ?: 'Sin telefono' }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-[#33413d]">Documento</dt>
                        <dd class="mt-1 text-[#64716d]">{{ $client->document_number ?: 'Sin documento' }}</dd>
                    </div>
                    <div>
                        <dt class="font-bold text-[#33413d]">Nacimiento</dt>
                        <dd class="mt-1 text-[#64716d]">{{ $client->birthdate?->format('d/m/Y') ?: 'Sin fecha' }}</dd>
                    </div>
                </dl>
            </section>

            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Actividad</h2>
                <div class="mt-4 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-md border border-[#e1e6e0] p-3">
                        <p class="text-xl font-bold">{{ $client->appointments_count }}</p>
                        <p class="text-xs text-[#64716d]">Total</p>
                    </div>
                    <div class="rounded-md border border-[#e1e6e0] p-3">
                        <p class="text-xl font-bold">{{ $client->pending_appointments_count }}</p>
                        <p class="text-xs text-[#64716d]">Pendientes</p>
                    </div>
                    <div class="rounded-md border border-[#e1e6e0] p-3">
                        <p class="text-xl font-bold">{{ $client->completed_appointments_count }}</p>
                        <p class="text-xs text-[#64716d]">Hechas</p>
                    </div>
                </div>
            </section>

            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Notas internas</h2>
                <p class="mt-3 whitespace-pre-line text-sm text-[#64716d]">{{ $client->notes ?: 'Sin notas registradas.' }}</p>
            </section>
        </aside>

        <main class="space-y-6">
            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Proxima cita</h2>
                @if ($nextAppointment)
                    <div class="mt-4 grid gap-3 rounded-md border border-[#e1e6e0] p-4 md:grid-cols-[1fr_auto] md:items-center">
                        <div>
                            <p class="font-bold">{{ $nextAppointment->starts_at->format('d/m/Y H:i') }} - {{ $nextAppointment->ends_at->format('H:i') }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">{{ $nextAppointment->service?->name ?: 'Servicio sin asignar' }} con {{ $nextAppointment->professional?->name ?: 'profesional sin asignar' }}</p>
                        </div>
                        <a class="rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('agenda.edit', $nextAppointment) }}">Editar</a>
                    </div>
                @else
                    <div class="mt-4 rounded-md border border-dashed border-[#cfd8d2] p-4 text-sm text-[#64716d]">
                        Este cliente no tiene citas proximas.
                    </div>
                @endif
            </section>

            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <h2 class="text-lg font-bold">Historial de citas</h2>
                    <p class="mt-1 text-sm text-[#64716d]">Citas pasadas y futuras asociadas a este cliente.</p>
                </div>

                @forelse ($appointments as $appointment)
                    <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[9rem_1fr_9rem_auto] md:items-center">
                        <div>
                            <p class="font-bold">{{ $appointment->starts_at->format('d/m/Y') }}</p>
                            <p class="text-sm text-[#64716d]">{{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}</p>
                        </div>
                        <div>
                            <p class="font-bold">{{ $appointment->service?->name ?: 'Servicio sin asignar' }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">{{ $appointment->professional?->name ?: 'Profesional sin asignar' }}</p>
                            @if ($appointment->notes)
                                <p class="mt-1 text-sm text-[#64716d]">{{ str($appointment->notes)->limit(100) }}</p>
                            @endif
                        </div>
                        <span class="w-fit rounded-md px-2 py-1 text-xs font-bold {{ $statusStyles[$appointment->status] ?? 'bg-[#f1f1ef] text-[#53615d]' }}">
                            {{ $statusLabels[$appointment->status] ?? ucfirst($appointment->status) }}
                        </span>
                        <a class="rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('agenda.edit', $appointment) }}">Abrir</a>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="font-bold">Sin historial todavia</p>
                        <p class="mt-2 text-sm text-[#64716d]">Cuando agendes citas para este cliente, apareceran aqui.</p>
                        <a class="trebbia-button mt-5" href="{{ route('agenda.create', ['client_id' => $client->id]) }}">Crear cita</a>
                    </div>
                @endforelse
            </section>

            <div>{{ $appointments->links() }}</div>
        </main>
    </div>
@endsection
