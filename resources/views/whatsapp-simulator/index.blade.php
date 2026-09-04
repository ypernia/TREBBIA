@extends('layouts.app')

@section('title', 'WhatsApp demo | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'WhatsApp demo')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[20rem_1fr]">
        <aside class="trebbia-card overflow-hidden">
            <div class="border-b border-[#e7ebe7] p-5">
                <h2 class="text-lg font-bold">Conversaciones</h2>
                <p class="mt-1 text-sm text-[#64716d]">Simulador interno sin conexion a Meta.</p>
            </div>
            <div class="divide-y divide-[#e7ebe7]">
                @forelse ($conversations as $item)
                    <a href="{{ route('whatsapp-simulator.index', ['conversation' => $item->id]) }}" class="block p-4 hover:bg-[#f8faf8] {{ $conversation?->id === $item->id ? 'bg-[#edf7f4]' : '' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold">{{ $item->whatsappContact?->name ?: $item->whatsappContact?->phone }}</p>
                                <p class="mt-1 text-sm text-[#64716d]">{{ $item->whatsappContact?->phone }}</p>
                            </div>
                            <span class="rounded-md bg-white px-2 py-1 text-xs font-bold text-[#245f57]">{{ $item->current_step }}</span>
                        </div>
                    </a>
                @empty
                    <p class="p-5 text-sm text-[#64716d]">Aun no hay conversaciones simuladas.</p>
                @endforelse
            </div>
        </aside>

        <main class="space-y-6">
            <section class="trebbia-card p-5">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Chat de prueba</h2>
                        <p class="mt-1 text-sm text-[#64716d]">Prueba frases como "quiero agendar", el nombre de un servicio, un profesional, una fecha y un horario.</p>
                    </div>
                    @if ($conversation)
                        <form method="POST" action="{{ route('whatsapp-simulator.reset', $conversation) }}">
                            @csrf
                            <button class="trebbia-button trebbia-button-secondary">Reiniciar</button>
                        </form>
                    @endif
                </div>

                <form method="POST" action="{{ route('whatsapp-simulator.store') }}" class="mt-5 grid gap-4 lg:grid-cols-[12rem_1fr]">
                    @csrf
                    <div>
                        <label class="trebbia-label" for="phone">Telefono</label>
                        <input class="trebbia-input" id="phone" name="phone" value="{{ old('phone', $conversation?->whatsappContact?->phone ?? '573001112233') }}" required>
                    </div>
                    <div>
                        <label class="trebbia-label" for="name">Nombre</label>
                        <input class="trebbia-input" id="name" name="name" value="{{ old('name', $conversation?->whatsappContact?->name) }}" placeholder="Cliente de prueba">
                    </div>
                    <div class="lg:col-span-2">
                        <label class="trebbia-label" for="body">Mensaje del cliente</label>
                        <textarea class="trebbia-input min-h-28" id="body" name="body" required placeholder="Hola, quiero agendar una cita">{{ old('body') }}</textarea>
                    </div>
                    <div class="lg:col-span-2">
                        <button class="trebbia-button w-full sm:w-auto">Enviar mensaje simulado</button>
                    </div>
                </form>
            </section>

            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-lg font-bold">{{ $conversation?->whatsappContact?->name ?: 'Sin conversacion seleccionada' }}</h2>
                            <p class="mt-1 text-sm text-[#64716d]">{{ $conversation?->whatsappContact?->phone ?: 'Escribe un mensaje para iniciar.' }}</p>
                        </div>
                        @if ($state)
                            <span class="w-fit rounded-md bg-[#edf7f4] px-3 py-2 text-xs font-bold text-[#245f57]">Estado: {{ $state->state }}</span>
                        @endif
                    </div>
                </div>

                <div class="min-h-96 space-y-4 bg-[#f8faf8] p-5">
                    @forelse ($messages as $message)
                        <div class="flex {{ $message->direction === 'outbound' ? 'justify-end' : 'justify-start' }}">
                            <div class="max-w-[min(34rem,85%)] rounded-lg px-4 py-3 shadow-sm {{ $message->direction === 'outbound' ? 'bg-[#245f57] text-white' : 'border border-[#e1e6e0] bg-white text-[#18211f]' }}">
                                <p class="whitespace-pre-line text-sm leading-6">{{ $message->body }}</p>
                                <p class="mt-2 text-[11px] font-semibold opacity-70">
                                    {{ $message->direction === 'outbound' ? 'TREBBIA' : 'Cliente' }} · {{ $message->created_at->format('d/m H:i') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-md border border-dashed border-[#cfd8d2] bg-white p-5 text-sm text-[#64716d]">
                            No hay mensajes todavia. Usa el formulario superior para iniciar una conversacion de prueba.
                        </div>
                    @endforelse
                </div>
            </section>
        </main>
    </div>
@endsection
