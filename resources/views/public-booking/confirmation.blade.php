<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reserva recibida | {{ $business->name }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-[#f6f7f4] font-sans text-[#18211f] antialiased">
    <main class="mx-auto flex min-h-screen max-w-3xl items-center px-5 py-8 sm:px-8">
        <section class="trebbia-card w-full p-8">
            <p class="text-sm font-bold text-[#245f57]">Reserva recibida</p>
            <h1 class="mt-3 text-3xl font-bold">{{ $business->name }}</h1>
            <p class="mt-3 text-[#64716d]">Tu solicitud de cita fue registrada correctamente.</p>

            <div class="mt-6 grid gap-3 rounded-md border border-[#e1e6e0] bg-[#f8faf8] p-5 text-sm text-[#53615d]">
                <p><span class="font-bold text-[#18211f]">Cliente:</span> {{ $appointment->client?->name }}</p>
                <p><span class="font-bold text-[#18211f]">Servicio:</span> {{ $appointment->service?->name }}</p>
                <p><span class="font-bold text-[#18211f]">Profesional:</span> {{ $appointment->professional?->name }}</p>
                <p><span class="font-bold text-[#18211f]">Fecha:</span> {{ $appointment->starts_at->format('d/m/Y') }}</p>
                <p><span class="font-bold text-[#18211f]">Hora:</span> {{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}</p>
                <p><span class="font-bold text-[#18211f]">Estado:</span> {{ $appointment->status === 'confirmed' ? 'Confirmada' : 'Pendiente de confirmacion' }}</p>
            </div>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a class="trebbia-button" href="{{ route('public-booking.show', $business->slug) }}">Crear otra reserva</a>
                <a class="trebbia-button trebbia-button-secondary" href="{{ route('home') }}">Volver</a>
            </div>
        </section>
    </main>
</body>
</html>
