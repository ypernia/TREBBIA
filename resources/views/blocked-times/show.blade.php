@extends('layouts.app')

@section('title', 'Resolver bloqueo | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Resolver bloqueo')

@section('content')
    @include('partials.errors')

    <section class="trebbia-card mb-6 overflow-hidden border border-[#b9dfd5] bg-[#edf8f5]">
        <div class="grid gap-4 p-5 lg:grid-cols-[1fr_auto] lg:items-center">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Bloqueo guardado</p>
                <h2 class="mt-1 text-2xl font-bold">{{ $blockedTime->starts_at->format('d/m/Y H:i') }} - {{ $blockedTime->ends_at->format('H:i') }}</h2>
                <p class="mt-1 text-sm leading-6 text-[#64716d]">
                    {{ $blockedTime->reason ?: 'Bloqueo de agenda' }}.
                    @if ($blockedTime->professional)
                        Profesional: {{ $blockedTime->professional->name }}.
                    @elseif ($blockedTime->resource)
                        Recurso: {{ $blockedTime->resource->name }}.
                    @else
                        Aplica a la agenda general.
                    @endif
                </p>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row">
                <a class="trebbia-button trebbia-button-secondary" href="{{ route('blocked-times.create', ['date' => $blockedTime->starts_at->toDateString()]) }}">Nuevo bloqueo</a>
                <a class="trebbia-button" href="{{ route('agenda.index', ['date' => $blockedTime->starts_at->toDateString()]) }}">Volver a agenda</a>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <section class="trebbia-card overflow-hidden">
            <div class="border-b border-[#e7ebe7] p-5">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Reprogramacion asistida</p>
                <h2 class="mt-1 text-xl font-bold">{{ $impact->count() }} cita{{ $impact->count() === 1 ? '' : 's' }} por resolver</h2>
                <p class="mt-1 text-sm text-[#64716d]">TREBBIA sugiere horarios libres, pero tu equipo decide que accion tomar.</p>
            </div>

            <div class="divide-y divide-[#e7ebe7]">
                @forelse ($impact as $appointment)
                    @php
                        $appointmentSuggestions = collect($suggestions[$appointment->id] ?? []);
                        $clientName = $appointment->client?->name ?: 'Cliente';
                        $oldSlot = $appointment->starts_at->format('d/m/Y H:i');
                        $suggestionText = $appointmentSuggestions
                            ->map(fn ($slot) => $slot->format('d/m/Y H:i'))
                            ->implode(', ');
                        $message = $suggestionText
                            ? "Hola {$clientName}, necesitamos reprogramar tu cita del {$oldSlot}. Tenemos estas opciones disponibles: {$suggestionText}. Cual te funciona mejor?"
                            : "Hola {$clientName}, necesitamos reprogramar tu cita del {$oldSlot}. Te escribimos para revisar una nueva disponibilidad.";
                    @endphp

                    <article class="p-5">
                        <div class="grid gap-4 lg:grid-cols-[1fr_auto] lg:items-start">
                            <div>
                                <p class="font-bold">{{ $appointment->starts_at->format('d/m/Y H:i') }} - {{ $appointment->service?->name ?: 'Servicio' }}</p>
                                <p class="mt-1 text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }} / {{ $appointment->professional?->name ?: 'Profesional sin asignar' }}</p>
                                @if ($appointment->resource)
                                    <p class="text-sm text-[#64716d]">Recurso: {{ $appointment->resource->name }}</p>
                                @endif
                            </div>
                            <a class="trebbia-button trebbia-button-secondary" href="{{ route('agenda.edit', $appointment) }}">Editar manualmente</a>
                        </div>

                        <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_18rem]">
                            <div>
                                <h3 class="text-sm font-bold uppercase tracking-[0.12em] text-[#64716d]">Opciones sugeridas</h3>
                                <div class="mt-3 grid gap-3 md:grid-cols-3">
                                    @forelse ($appointmentSuggestions as $slot)
                                        <form method="POST" action="{{ route('agenda.reschedule', $appointment) }}" class="rounded-md border border-[#e1e6e0] bg-white p-4">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="date" value="{{ $slot->toDateString() }}">
                                            <input type="hidden" name="starts_at" value="{{ $slot->format('H:i') }}">
                                            <input type="hidden" name="return_to" value="{{ url()->current() }}">
                                            <p class="text-sm font-bold">{{ $slot->format('d/m/Y') }}</p>
                                            <p class="mt-1 text-2xl font-bold text-[#245f57]">{{ $slot->format('H:i') }}</p>
                                            <button class="trebbia-button mt-3 w-full">Reprogramar</button>
                                        </form>
                                    @empty
                                        <p class="rounded-md border border-dashed border-[#cfd8d2] p-4 text-sm text-[#64716d] md:col-span-3">No encontramos opciones cercanas con el mismo profesional. Revisa manualmente otra fecha o profesional.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="rounded-md border border-[#e1e6e0] bg-[#fbfcfb] p-4">
                                <h3 class="text-sm font-bold uppercase tracking-[0.12em] text-[#64716d]">Mensaje sugerido</h3>
                                <p class="mt-3 text-sm leading-6 text-[#53615d]">{{ $message }}</p>
                                <form method="POST" action="{{ route('agenda.contact-pending', $appointment) }}" class="mt-4">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="return_to" value="{{ url()->current() }}">
                                    <button class="trebbia-button trebbia-button-secondary w-full">Marcar por contactar</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <x-empty-state
                        icon="calendar"
                        title="No hay citas por resolver"
                        body="Este bloqueo no afecta citas existentes. La disponibilidad ya quedo protegida para nuevas reservas."
                        :action="route('agenda.index', ['date' => $blockedTime->starts_at->toDateString()])"
                        action-label="Volver a agenda"
                    />
                @endforelse
            </div>
        </section>

        <aside class="space-y-6">
            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Como usarlo</h2>
                <div class="mt-4 space-y-3 text-sm leading-6 text-[#53615d]">
                    <p><span class="font-bold text-[#18211f]">Reprogramar:</span> mueve la cita al horario sugerido y la deja pendiente de contacto.</p>
                    <p><span class="font-bold text-[#18211f]">Marcar por contactar:</span> conserva la cita actual, pero deja la alerta para gestionarla con el cliente.</p>
                    <p><span class="font-bold text-[#18211f]">Editar manualmente:</span> permite cambiar profesional, recurso, estado o notas.</p>
                </div>
            </section>
        </aside>
    </div>
@endsection
