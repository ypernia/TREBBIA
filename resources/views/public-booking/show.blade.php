<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reservar | {{ $business->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-[#f6f7f4] font-sans text-[#18211f] antialiased">
    <main class="mx-auto min-h-screen max-w-5xl px-5 py-8 sm:px-8">
        <header class="mb-6 flex flex-col gap-2 border-b border-[#e1e6e0] pb-6">
            <p class="text-sm font-semibold text-[#64716d]">Reserva online</p>
            <h1 class="text-3xl font-bold">{{ $business->name }}</h1>
            <p class="max-w-2xl text-[#64716d]">{{ $business->industry ?: 'Agenda tu cita en linea' }}</p>
        </header>

        @if ($errors->any())
            <div class="mb-5 rounded-md border border-[#f0c9c4] bg-[#fff4f2] px-4 py-3 text-sm font-semibold text-[#8a3027]">
                Revisa la informacion de tu reserva.
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1fr_20rem]">
            <section class="trebbia-card p-6">
                <h2 class="text-xl font-bold">Selecciona tu cita</h2>

                <form method="GET" action="{{ route('public-booking.show', $business->slug) }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="trebbia-label" for="service_id">Servicio</label>
                        <select class="trebbia-input" id="service_id" name="service_id" required>
                            <option value="">Seleccionar</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}" @selected($selectedService?->id === $service->id)>{{ $service->name }} - {{ $service->duration_minutes }} min</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="trebbia-label" for="professional_id">Profesional</label>
                        <select class="trebbia-input" id="professional_id" name="professional_id" @disabled(! $selectedService)>
                            <option value="">Seleccionar</option>
                            @foreach ($professionals as $professional)
                                <option value="{{ $professional->id }}" @selected($selectedProfessional?->id === $professional->id)>{{ $professional->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="trebbia-label" for="date">Fecha</label>
                        <input class="trebbia-input" id="date" type="date" name="date" value="{{ $date->format('Y-m-d') }}" required>
                    </div>
                    <div class="flex items-end">
                        <button class="trebbia-button trebbia-button-secondary w-full">Buscar horarios</button>
                    </div>
                </form>

                <form method="POST" action="{{ route('public-booking.store', $business->slug) }}" class="mt-6 grid gap-4">
                    @csrf
                    <input type="hidden" name="service_id" value="{{ $selectedService?->id }}">
                    <input type="hidden" name="professional_id" value="{{ $selectedProfessional?->id }}">
                    <input type="hidden" name="date" value="{{ $date->format('Y-m-d') }}">

                    <div>
                        <p class="trebbia-label">Horarios disponibles</p>
                        <div class="grid gap-2 sm:grid-cols-4">
                            @forelse ($availableSlots as $slot)
                                <label class="flex cursor-pointer items-center justify-center rounded-md border border-[#d7ddd7] bg-white px-3 py-2 text-sm font-bold text-[#245f57] has-[:checked]:border-[#245f57] has-[:checked]:bg-[#edf7f4]">
                                    <input class="sr-only" type="radio" name="starts_at" value="{{ $slot->format('H:i') }}" required>
                                    {{ $slot->format('H:i') }}
                                </label>
                            @empty
                                <p class="rounded-md border border-dashed border-[#cfd8d2] p-4 text-sm text-[#64716d] sm:col-span-4">Selecciona servicio, profesional y fecha para ver horarios disponibles.</p>
                            @endforelse
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="trebbia-label" for="client_name">Nombre</label>
                            <input class="trebbia-input" id="client_name" name="client_name" value="{{ old('client_name') }}" required>
                        </div>
                        <div>
                            <label class="trebbia-label" for="client_email">Correo</label>
                            <input class="trebbia-input" id="client_email" type="email" name="client_email" value="{{ old('client_email') }}">
                        </div>
                        <div>
                            <label class="trebbia-label" for="client_phone">Telefono</label>
                            <input class="trebbia-input" id="client_phone" name="client_phone" value="{{ old('client_phone') }}">
                        </div>
                        <div class="sm:col-span-2">
                            <label class="trebbia-label" for="notes">Notas</label>
                            <textarea class="trebbia-input" id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <button class="trebbia-button" @disabled(! $selectedService || ! $selectedProfessional || $availableSlots->isEmpty())>Confirmar solicitud</button>
                </form>
            </section>

            <aside class="space-y-6">
                <section class="trebbia-card p-5">
                    <h2 class="text-lg font-bold">Detalle</h2>
                    <div class="mt-4 space-y-3 text-sm text-[#53615d]">
                        <p><span class="font-bold text-[#18211f]">Servicio:</span> {{ $selectedService?->name ?: 'Por seleccionar' }}</p>
                        <p><span class="font-bold text-[#18211f]">Profesional:</span> {{ $selectedProfessional?->name ?: 'Por seleccionar' }}</p>
                        <p><span class="font-bold text-[#18211f]">Fecha:</span> {{ $date->format('d/m/Y') }}</p>
                        <p><span class="font-bold text-[#18211f]">Confirmacion:</span> {{ ($settings->public_booking_settings['require_manual_confirmation'] ?? true) ? 'Manual' : 'Automatica' }}</p>
                    </div>
                </section>

                <section class="trebbia-card p-5">
                    <h2 class="text-lg font-bold">Contacto</h2>
                    <div class="mt-4 space-y-3 text-sm text-[#53615d]">
                        <p>{{ $business->email ?: 'Sin correo publico' }}</p>
                        <p>{{ $business->phone ?: 'Sin telefono publico' }}</p>
                    </div>
                </section>
            </aside>
        </div>
    </main>
</body>
</html>
