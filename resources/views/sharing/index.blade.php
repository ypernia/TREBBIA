@extends('layouts.app')

@section('title', 'Compartir reservas | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Compartir reservas')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <main class="space-y-6">
            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#64716d]">Centro de reservas</p>
                            <h2 class="mt-1 text-2xl font-bold">Listo para compartir</h2>
                            <p class="mt-2 max-w-2xl text-sm text-[#64716d]">Usa estos enlaces para que tus clientes reserven por la web o escriban por WhatsApp sin configuraciones tecnicas.</p>
                        </div>
                        <div class="rounded-md border border-[#d7ddd7] bg-[#f8faf8] px-4 py-3">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#64716d]">Activacion</p>
                            <p class="mt-1 text-lg font-bold text-[#245f57]">{{ $share['percent'] }}%</p>
                            <p class="mt-1 text-sm text-[#53615d]">{{ $share['completed'] }} de {{ $share['total'] }} pasos</p>
                        </div>
                    </div>
                    <div class="mt-5 h-2 overflow-hidden rounded-full bg-[#edf2ef]">
                        <div class="h-full rounded-full bg-[#245f57]" style="width: {{ $share['percent'] }}%"></div>
                    </div>
                </div>

                <div class="grid gap-4 p-6 md:grid-cols-2">
                    <div class="rounded-md border border-[#d7ddd7] bg-white p-5">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Pagina publica</p>
                        <h3 class="mt-2 text-lg font-bold">Reserva web</h3>
                        <p class="mt-2 break-all rounded-md border border-[#e1e6e0] bg-[#fbfcfb] p-3 text-sm font-semibold text-[#245f57]" data-copy-value="{{ $share['public_url'] }}">{{ $share['public_url'] }}</p>
                        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                            <button class="trebbia-button" type="button" data-copy-target="{{ $share['public_url'] }}">Copiar enlace</button>
                            <a class="trebbia-button trebbia-button-secondary" href="{{ $share['public_url'] }}" target="_blank">Abrir</a>
                        </div>
                    </div>

                    <div class="rounded-md border border-[#d7ddd7] bg-white p-5">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">WhatsApp</p>
                        <h3 class="mt-2 text-lg font-bold">Mensaje directo</h3>
                        @if ($share['whatsapp_url'])
                            <p class="mt-2 break-all rounded-md border border-[#e1e6e0] bg-[#fbfcfb] p-3 text-sm font-semibold text-[#245f57]">{{ $share['whatsapp_url'] }}</p>
                            <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                                <button class="trebbia-button" type="button" data-copy-target="{{ $share['whatsapp_url'] }}">Copiar enlace</button>
                                <a class="trebbia-button trebbia-button-secondary" href="{{ $share['whatsapp_url'] }}" target="_blank">Abrir</a>
                            </div>
                        @else
                            <p class="mt-2 rounded-md border border-dashed border-[#cfd8d2] p-4 text-sm text-[#64716d]">Configura el numero de WhatsApp para generar este enlace.</p>
                            <a class="mt-4 inline-flex text-sm font-bold text-[#245f57] hover:underline" href="{{ route('settings.index') }}#whatsapp-channel">Configurar WhatsApp</a>
                        @endif
                    </div>
                </div>
            </section>

            <section class="grid gap-6 lg:grid-cols-2">
                <div class="trebbia-card p-6">
                    <h2 class="text-xl font-bold">QR pagina publica</h2>
                    <div class="mt-5 rounded-md border border-[#e1e6e0] bg-white p-5">
                        <img class="mx-auto h-56 w-56" src="{{ $share['public_qr'] }}" alt="QR pagina publica {{ $business->name }}">
                    </div>
                    <button class="trebbia-button trebbia-button-secondary mt-4 w-full" type="button" data-copy-target="{{ $share['public_url'] }}">Copiar enlace del QR</button>
                </div>

                <div class="trebbia-card p-6">
                    <h2 class="text-xl font-bold">QR WhatsApp</h2>
                    @if ($share['whatsapp_qr'])
                        <div class="mt-5 rounded-md border border-[#e1e6e0] bg-white p-5">
                            <img class="mx-auto h-56 w-56" src="{{ $share['whatsapp_qr'] }}" alt="QR WhatsApp {{ $business->name }}">
                        </div>
                        <button class="trebbia-button trebbia-button-secondary mt-4 w-full" type="button" data-copy-target="{{ $share['whatsapp_url'] }}">Copiar enlace del QR</button>
                    @else
                        <div class="mt-5 rounded-md border border-dashed border-[#cfd8d2] bg-[#f8faf8] p-8 text-center text-sm text-[#64716d]">El QR de WhatsApp aparece cuando el canal esta activo.</div>
                    @endif
                </div>
            </section>

            <section class="trebbia-card p-6">
                <h2 class="text-xl font-bold">Mensajes sugeridos</h2>
                <div class="mt-5 grid min-w-0 gap-4 lg:grid-cols-3">
                    @foreach ($share['messages'] as $label => $message)
                        <div class="flex min-w-0 flex-col rounded-md border border-[#e1e6e0] bg-white p-4">
                            <p class="text-sm font-bold uppercase tracking-[0.14em] text-[#64716d]">{{ str_replace('_', ' ', $label) }}</p>
                            <p class="mt-3 min-w-0 flex-1 whitespace-pre-line break-words text-sm leading-6 text-[#53615d] [overflow-wrap:anywhere]">{{ $message }}</p>
                            <button class="mt-4 text-sm font-bold text-[#245f57] hover:underline" type="button" data-copy-target="{{ $message }}">Copiar texto</button>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>

        <aside class="space-y-6">
            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <h2 class="text-lg font-bold">Checklist de activacion</h2>
                    <p class="mt-1 text-sm text-[#64716d]">Completa estos puntos antes de compartir masivamente.</p>
                </div>
                <div class="divide-y divide-[#e7ebe7]">
                    @foreach ($share['checklist'] as $item)
                        <a class="block p-4 hover:bg-[#f8faf8]" href="{{ $item['action'] }}">
                            <div class="flex items-start gap-3">
                                <span class="mt-1 h-3 w-3 rounded-full {{ $item['complete'] ? 'bg-[#245f57]' : 'bg-[#cfd8d2]' }}"></span>
                                <div>
                                    <p class="font-bold">{{ $item['label'] }}</p>
                                    <p class="mt-1 text-sm text-[#64716d]">{{ $item['description'] }}</p>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>

            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Siguiente accion</h2>
                <p class="mt-3 text-sm text-[#64716d]">Comparte primero el enlace publico con clientes cercanos. Cuando quieras automatizar respuestas reales por WhatsApp, solicita la activacion administrada.</p>
                <a class="trebbia-button mt-4 w-full text-center" href="{{ route('public-booking.show', $business->slug) }}" target="_blank">Ver experiencia del cliente</a>
                <a class="trebbia-button trebbia-button-secondary mt-3 w-full text-center" href="{{ route('whatsapp-activation.create') }}">Solicitar WhatsApp automatico</a>
            </section>
        </aside>
    </div>

    <script>
        document.querySelectorAll('[data-copy-target]').forEach((button) => {
            button.addEventListener('click', async () => {
                await navigator.clipboard.writeText(button.dataset.copyTarget);
                const original = button.textContent;
                button.textContent = 'Copiado';
                setTimeout(() => button.textContent = original, 1400);
            });
        });
    </script>
@endsection
