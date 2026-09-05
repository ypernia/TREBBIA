@extends('layouts.app')

@section('title', 'Contacto WhatsApp | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Contacto WhatsApp')

@section('content')
    @include('partials.errors')

    @php
        $contactActions = [
            \App\Models\Appointment::CONTACT_SENT => 'Mensaje enviado',
            \App\Models\Appointment::CONTACT_CONFIRMED => 'Cliente confirmo',
            \App\Models\Appointment::CONTACT_NO_RESPONSE => 'No respondio',
            \App\Models\Appointment::CONTACT_CALL_REQUIRED => 'Requiere llamada',
        ];

        $appointmentMessage = function ($appointment) {
            $client = $appointment->client?->name ?: 'Cliente';
            $date = $appointment->starts_at->format('d/m/Y H:i');

            return "Hola {$client}, te escribimos de {$appointment->business->name} para confirmar el cambio de tu cita para {$date}. Nos confirmas si puedes asistir?";
        };

        $requestMessage = function ($bookingRequest) {
            $client = $bookingRequest->client?->name ?: 'Cliente';
            $date = $bookingRequest->starts_at->format('d/m/Y H:i');

            return "Hola {$client}, recibimos tu solicitud de reserva para {$date}. Vamos a confirmar disponibilidad y te respondemos por este medio.";
        };

        $phoneLink = function (?string $phone, string $message) {
            $cleanPhone = preg_replace('/\D+/', '', $phone ?? '');

            return $cleanPhone ? 'https://wa.me/'.$cleanPhone.'?text='.rawurlencode($message) : null;
        };
    @endphp

    <section class="trebbia-card mb-6 overflow-hidden border border-[#b9dfd5] bg-[#edf8f5]">
        <div class="grid gap-5 p-5 xl:grid-cols-[1fr_auto] xl:items-center">
            <div>
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Bandeja manual</p>
                <h2 class="mt-1 text-2xl font-bold">Todo lo que debes gestionar por WhatsApp</h2>
                <p class="mt-1 max-w-3xl text-sm leading-6 text-[#64716d]">Centraliza mensajes sugeridos, solicitudes pendientes y citas reprogramadas. Por ahora el envio es manual; esta misma bandeja sera la base del WhatsApp automatico.</p>
            </div>
            <div class="grid grid-cols-2 gap-3 text-center sm:min-w-[28rem] lg:grid-cols-4">
                <div class="rounded-md bg-white/70 p-3">
                    <p class="text-2xl font-bold">{{ $metrics['followUps'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#64716d]">Contactar</p>
                </div>
                <div class="rounded-md bg-white/70 p-3">
                    <p class="text-2xl font-bold">{{ $metrics['pendingRequests'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#64716d]">Solicitudes</p>
                </div>
                <div class="rounded-md bg-white/70 p-3">
                    <p class="text-2xl font-bold">{{ $metrics['noResponse'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#64716d]">Sin respuesta</p>
                </div>
                <div class="rounded-md bg-white/70 p-3">
                    <p class="text-2xl font-bold">{{ $metrics['callRequired'] }}</p>
                    <p class="text-xs font-bold uppercase tracking-[0.12em] text-[#64716d]">Llamar</p>
                </div>
            </div>
        </div>
    </section>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <section class="space-y-6">
            <div class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Urgente</p>
                    <h2 class="mt-1 text-lg font-bold">Citas que necesitan contacto</h2>
                </div>
                <div class="divide-y divide-[#e7ebe7]">
                    @forelse ($urgent as $appointment)
                        @php
                            $message = $appointmentMessage($appointment);
                            $whatsappUrl = $phoneLink($appointment->client?->phone, $message);
                        @endphp
                        <article class="grid gap-4 p-5 lg:grid-cols-[1fr_18rem]">
                            <div>
                                <p class="font-bold">{{ $appointment->starts_at->format('d/m/Y H:i') }} - {{ $appointment->service?->name }}</p>
                                <p class="mt-1 text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }} / {{ $appointment->professional?->name ?: 'Profesional sin asignar' }}</p>
                                <span class="mt-3 inline-flex rounded-md bg-[#fff7ed] px-2 py-1 text-xs font-bold text-[#8a3027]">{{ $appointment->contactStatusLabel() }}</span>
                                <p id="appointment-message-{{ $appointment->id }}" class="mt-3 text-sm leading-6 text-[#53615d]">{{ $message }}</p>
                            </div>
                            <div class="space-y-2">
                                <button class="trebbia-button trebbia-button-secondary w-full" type="button" data-copy-target="appointment-message-{{ $appointment->id }}">Copiar mensaje</button>
                                @if ($whatsappUrl)
                                    <a class="trebbia-button w-full text-center" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Abrir WhatsApp</a>
                                @endif
                                @foreach ($contactActions as $status => $label)
                                    <form method="POST" action="{{ route('agenda.contact-status', $appointment) }}">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="return_to" value="{{ url()->current() }}">
                                        <input type="hidden" name="contact_status" value="{{ $status }}">
                                        <button class="w-full rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57] hover:bg-[#edf7f4]">{{ $label }}</button>
                                    </form>
                                @endforeach
                            </div>
                        </article>
                    @empty
                        <x-empty-state
                            icon="message"
                            title="No hay contactos urgentes"
                            body="Cuando una cita reprogramada quede pendiente, aparecera aqui."
                        />
                    @endforelse
                </div>
            </div>

            <div class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Reservas</p>
                    <h2 class="mt-1 text-lg font-bold">Solicitudes pendientes</h2>
                </div>
                <div class="divide-y divide-[#e7ebe7]">
                    @forelse ($pendingRequests as $bookingRequest)
                        @php
                            $message = $requestMessage($bookingRequest);
                            $whatsappUrl = $phoneLink($bookingRequest->client?->phone, $message);
                        @endphp
                        <article class="grid gap-4 p-5 lg:grid-cols-[1fr_18rem]">
                            <div>
                                <p class="font-bold">{{ $bookingRequest->starts_at->format('d/m/Y H:i') }} - {{ $bookingRequest->service?->name }}</p>
                                <p class="mt-1 text-sm text-[#64716d]">{{ $bookingRequest->client?->name ?: 'Cliente sin asignar' }} / {{ $bookingRequest->professional?->name ?: 'Profesional sin asignar' }}</p>
                                <p id="request-message-{{ $bookingRequest->id }}" class="mt-3 text-sm leading-6 text-[#53615d]">{{ $message }}</p>
                            </div>
                            <div class="space-y-2">
                                <button class="trebbia-button trebbia-button-secondary w-full" type="button" data-copy-target="request-message-{{ $bookingRequest->id }}">Copiar mensaje</button>
                                @if ($whatsappUrl)
                                    <a class="trebbia-button w-full text-center" href="{{ $whatsappUrl }}" target="_blank" rel="noopener">Abrir WhatsApp</a>
                                @endif
                                <form method="POST" action="{{ route('booking-requests.accept', $bookingRequest) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="w-full rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57] hover:bg-[#edf7f4]">Aceptar reserva</button>
                                </form>
                                <form method="POST" action="{{ route('booking-requests.reject', $bookingRequest) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="w-full rounded-md border border-[#f0c9c4] px-3 py-2 text-sm font-bold text-[#8a3027] hover:bg-[#fff4f2]">Rechazar</button>
                                </form>
                            </div>
                        </article>
                    @empty
                        <x-empty-state
                            icon="calendar"
                            title="No hay solicitudes pendientes"
                            body="Las reservas publicas pendientes apareceran aqui para responder rapido."
                        />
                    @endforelse
                </div>
            </div>
        </section>

        <aside class="space-y-6">
            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Canal actual</h2>
                <div class="mt-4 space-y-3 text-sm leading-6 text-[#53615d]">
                    <p><span class="font-bold text-[#18211f]">Pagina publica:</span></p>
                    <p class="break-all font-semibold text-[#245f57]">{{ $share['public_url'] }}</p>
                    <p><span class="font-bold text-[#18211f]">WhatsApp:</span></p>
                    @if ($share['whatsapp_url'])
                        <a class="break-all font-semibold text-[#245f57] hover:underline" href="{{ $share['whatsapp_url'] }}" target="_blank" rel="noopener">{{ $share['whatsapp_url'] }}</a>
                    @else
                        <p>Configura el numero del negocio para generar enlaces directos.</p>
                    @endif
                </div>
                <div class="mt-5 grid gap-2">
                    <a class="trebbia-button trebbia-button-secondary text-center" href="{{ route('settings.index') }}#whatsapp-channel">Configurar WhatsApp</a>
                    <a class="trebbia-button text-center" href="{{ route('sharing.index') }}">Compartir reservas</a>
                </div>
            </section>

            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Mas adelante</h2>
                <p class="mt-2 text-sm leading-6 text-[#64716d]">Cuando conectemos WhatsApp real, esta bandeja recibira mensajes, respuestas y confirmaciones automaticamente.</p>
            </section>
        </aside>
    </div>

    <script>
        document.querySelectorAll('[data-copy-target]').forEach((button) => {
            button.addEventListener('click', async () => {
                const target = document.getElementById(button.dataset.copyTarget);

                if (! target) {
                    return;
                }

                await navigator.clipboard.writeText(target.textContent.trim());
                button.textContent = 'Mensaje copiado';
            });
        });
    </script>
@endsection
