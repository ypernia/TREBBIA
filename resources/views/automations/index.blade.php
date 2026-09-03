@extends('layouts.app')

@section('title', 'Automatizaciones | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Automatizaciones')

@section('content')
    @php
        $statusLabels = [
            'pending' => 'Pendiente',
            'sent' => 'Enviado',
            'skipped' => 'Omitido',
        ];
        $statusStyles = [
            'pending' => 'bg-[#fff9eb] text-[#765214]',
            'sent' => 'bg-[#edf7f4] text-[#245f57]',
            'skipped' => 'bg-[#f1f1ef] text-[#53615d]',
        ];
    @endphp

    <div class="grid gap-4 md:grid-cols-3">
        <div class="trebbia-card p-5">
            <p class="text-sm font-semibold text-[#64716d]">Pendientes</p>
            <p class="mt-2 text-3xl font-bold">{{ $pendingCount }}</p>
        </div>
        <div class="trebbia-card p-5">
            <p class="text-sm font-semibold text-[#64716d]">Enviados</p>
            <p class="mt-2 text-3xl font-bold">{{ $sentCount }}</p>
        </div>
        <div class="trebbia-card p-5">
            <p class="text-sm font-semibold text-[#64716d]">Omitidos</p>
            <p class="mt-2 text-3xl font-bold">{{ $skippedCount }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-[1fr_24rem]">
        <main class="space-y-6">
            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <h2 class="text-lg font-bold">Citas listas para recordatorio</h2>
                    <p class="mt-1 text-sm text-[#64716d]">Programa recordatorios manuales, email o WhatsApp para conectar integraciones despues.</p>
                </div>

                @forelse ($pendingAppointments as $appointment)
                    <div class="grid gap-4 border-b border-[#e7ebe7] p-5 lg:grid-cols-[1fr_22rem] lg:items-start">
                        <div>
                            <p class="font-bold">{{ $appointment->starts_at->format('d/m/Y H:i') }} - {{ $appointment->ends_at->format('H:i') }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }} - {{ $appointment->service?->name ?: 'Servicio sin asignar' }}</p>
                            <p class="text-sm text-[#64716d]">{{ $appointment->professional?->name ?: 'Profesional sin asignar' }}</p>
                        </div>
                        <form method="POST" action="{{ route('automations.reminders.schedule', $appointment) }}" class="grid gap-3">
                            @csrf
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="trebbia-label" for="template_{{ $appointment->id }}">Plantilla</label>
                                    <select class="trebbia-input" id="template_{{ $appointment->id }}" name="notification_template_id">
                                        @foreach ($templates->where('is_active', true) as $template)
                                            <option value="{{ $template->id }}">{{ $template->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="trebbia-label" for="channel_{{ $appointment->id }}">Canal</label>
                                    <select class="trebbia-input" id="channel_{{ $appointment->id }}" name="channel">
                                        <option value="manual">Manual</option>
                                        <option value="email">Email</option>
                                        <option value="whatsapp">WhatsApp</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label class="trebbia-label" for="scheduled_for_{{ $appointment->id }}">Programado para</label>
                                <input class="trebbia-input" id="scheduled_for_{{ $appointment->id }}" type="datetime-local" name="scheduled_for" value="{{ $appointment->starts_at->subDay()->format('Y-m-d\TH:i') }}" required>
                            </div>
                            <button class="trebbia-button">Programar recordatorio</button>
                        </form>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="font-bold">No hay citas pendientes de recordatorio</p>
                        <p class="mt-2 text-sm text-[#64716d]">Cuando existan citas futuras sin recordatorio, apareceran aqui.</p>
                    </div>
                @endforelse
            </section>

            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <h2 class="text-lg font-bold">Recordatorios</h2>
                    <p class="mt-1 text-sm text-[#64716d]">Registro operativo para controlar envios mientras conectamos canales reales.</p>
                </div>

                @forelse ($reminders as $reminder)
                    <div class="grid gap-3 border-b border-[#e7ebe7] p-5 lg:grid-cols-[10rem_1fr_8rem_12rem] lg:items-center">
                        <div>
                            <p class="font-bold">{{ $reminder->scheduled_for->format('d/m/Y') }}</p>
                            <p class="text-sm text-[#64716d]">{{ $reminder->scheduled_for->format('H:i') }}</p>
                        </div>
                        <div>
                            <p class="font-bold">{{ $reminder->appointment?->client?->name ?: 'Cliente sin asignar' }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">{{ $reminder->message_snapshot ?: 'Sin mensaje guardado' }}</p>
                            <p class="mt-1 text-xs font-semibold text-[#64716d]">Canal: {{ ucfirst($reminder->channel) }}</p>
                        </div>
                        <span class="w-fit rounded-md px-2 py-1 text-xs font-bold {{ $statusStyles[$reminder->status] ?? 'bg-[#f1f1ef] text-[#53615d]' }}">
                            {{ $statusLabels[$reminder->status] ?? ucfirst($reminder->status) }}
                        </span>
                        <div class="flex flex-wrap gap-2 lg:justify-end">
                            @if ($reminder->status === 'pending')
                                <form method="POST" action="{{ route('automations.reminders.sent', $reminder) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-md border border-[#cfe4da] px-3 py-2 text-sm font-bold text-[#245f57]">Enviado</button>
                                </form>
                                <form method="POST" action="{{ route('automations.reminders.skip', $reminder) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#53615d]">Omitir</button>
                                </form>
                            @else
                                <p class="text-sm text-[#64716d]">{{ $reminder->sent_at?->format('d/m/Y H:i') ?: 'Sin envio' }}</p>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="font-bold">Sin recordatorios todavia</p>
                        <p class="mt-2 text-sm text-[#64716d]">Programa el primero desde una cita futura.</p>
                    </div>
                @endforelse
            </section>

            <div>{{ $reminders->links() }}</div>
        </main>

        <aside class="trebbia-card p-5">
            <h2 class="text-lg font-bold">Plantillas</h2>

            <form method="POST" action="{{ route('automations.templates.store') }}" class="mt-4 grid gap-3">
                @csrf
                <div>
                    <label class="trebbia-label" for="name">Nombre</label>
                    <input class="trebbia-input" id="name" name="name" value="Recordatorio de cita" required>
                </div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label class="trebbia-label" for="channel">Canal</label>
                        <select class="trebbia-input" id="channel" name="channel">
                            <option value="manual">Manual</option>
                            <option value="email">Email</option>
                            <option value="whatsapp">WhatsApp</option>
                        </select>
                    </div>
                    <div>
                        <label class="trebbia-label" for="trigger">Evento</label>
                        <select class="trebbia-input" id="trigger" name="trigger">
                            <option value="appointment_reminder">Recordatorio</option>
                            <option value="appointment_confirmation">Confirmacion</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="trebbia-label" for="subject">Asunto</label>
                    <input class="trebbia-input" id="subject" name="subject" value="Recordatorio de cita">
                </div>
                <div>
                    <label class="trebbia-label" for="body">Mensaje</label>
                    <textarea class="trebbia-input" id="body" name="body" rows="5" required>Hola {cliente}, te recordamos tu cita de {servicio} el {fecha} a las {hora}.</textarea>
                </div>
                <input type="hidden" name="is_active" value="1">
                <button class="trebbia-button">Crear plantilla</button>
            </form>

            <div class="mt-6 space-y-3">
                @foreach ($templates as $template)
                    <div class="rounded-md border border-[#e1e6e0] p-3">
                        <div class="flex items-start justify-between gap-3">
                            <p class="font-bold">{{ $template->name }}</p>
                            <span class="rounded-md px-2 py-1 text-xs font-bold {{ $template->is_active ? 'bg-[#edf7f4] text-[#245f57]' : 'bg-[#f1f1ef] text-[#53615d]' }}">{{ $template->is_active ? 'Activa' : 'Inactiva' }}</span>
                        </div>
                        <p class="mt-2 text-sm text-[#64716d]">{{ str($template->body)->limit(120) }}</p>
                    </div>
                @endforeach
            </div>
        </aside>
    </div>
@endsection
