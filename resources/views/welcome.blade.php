@extends('layouts.auth')

@section('title', 'trebbia. | Centro Inteligente de Reservas y Agendamiento')

@section('content')
    @php
        $navItems = [
            ['label' => 'La plataforma', 'href' => '#plataforma'],
            ['label' => 'Canales', 'href' => '#canales'],
            ['label' => 'Así funciona', 'href' => '#funciona'],
            ['label' => 'Beneficios', 'href' => '#beneficios'],
            ['label' => 'Capacidades', 'href' => '#capacidades'],
            ['label' => 'Planes', 'href' => '#planes'],
        ];

        $channels = [
            ['name' => 'WhatsApp', 'copy' => 'Solicitudes por chat convertidas en citas con fecha, servicio y responsable.'],
            ['name' => 'Enlace público', 'copy' => 'Reservas directas cuando el cliente prefiere elegir su horario.'],
            ['name' => 'Recepción', 'copy' => 'Citas registradas por tu equipo desde llamadas, visitas o mensajes.'],
            ['name' => 'Redes sociales', 'copy' => 'Conversaciones de campañas y perfiles organizadas en una agenda real.'],
        ];

        $steps = [
            ['title' => 'Recibe', 'copy' => 'El cliente agenda o solicita una cita desde cualquier canal.'],
            ['title' => 'Valida', 'copy' => 'TREBBIA cruza horario, profesional, servicio y disponibilidad.'],
            ['title' => 'Confirma', 'copy' => 'Tu equipo registra la reserva y mantiene el seguimiento.'],
        ];

        $benefits = [
            'Agenda clara',
            'Menos ausencias',
            'Clientes con historial',
            'Equipo sincronizado',
            'Reservas multicanal',
            'Decisiones con datos',
        ];

        $capabilities = [
            'Agenda inteligente',
            'WhatsApp',
            'Reserva pública',
            'Clientes',
            'Servicios',
            'Profesionales',
            'Recursos',
            'Automatizaciones',
            'Reportes',
            'Roles',
            'Planes',
            'Multi-negocio',
        ];

        $plans = [
            ['name' => 'Starter', 'copy' => 'Para negocios que quieren ordenar su agenda y empezar a recibir reservas con más claridad.'],
            ['name' => 'Pro', 'copy' => 'Para equipos que necesitan automatizaciones, reportes y operación con varios profesionales.'],
            ['name' => 'Business', 'copy' => 'Para operaciones con más capacidad, roles internos y control por negocio o sede.'],
        ];
    @endphp

    <div class="min-h-screen bg-[var(--trebbia-bg)] text-[var(--trebbia-ink)]">
        <header class="sticky top-0 z-40 border-b border-[var(--trebbia-line)] bg-[rgba(251,250,247,0.9)] backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-5 lg:px-8">
                <a href="{{ route('home') }}" class="shrink-0" aria-label="trebbia">
                    <x-trebbia-logo class="w-60 sm:w-72" />
                </a>

                <nav class="hidden items-center gap-7 lg:flex" aria-label="Menú principal">
                    @foreach ($navItems as $item)
                        <a class="text-sm font-medium text-[var(--trebbia-muted)] hover:text-[var(--trebbia-petrol)]" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-2">
                    <a class="hidden rounded-md px-4 py-2 text-sm font-medium text-[var(--trebbia-muted)] hover:bg-white sm:inline-flex" href="{{ route('login') }}">Entrar</a>
                    <a class="rounded-md bg-[var(--trebbia-petrol)] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[var(--trebbia-petrol-dark)]" href="{{ route('register') }}">Crear cuenta</a>
                </div>
            </div>
        </header>

        <main>
            <section class="overflow-hidden px-5 pb-16 pt-12 sm:pt-16 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                    <div>
                        <x-trebbia-logo class="mb-8 w-80 sm:w-[26rem]" />
                        <p class="trebbia-commercial-kicker">Reservas multicanal</p>
                        <h1 class="trebbia-commercial-title mt-5 max-w-3xl text-4xl sm:text-5xl lg:text-6xl">
                            Centraliza tus reservas. Atiende mejor. Crece con orden.
                        </h1>
                        <p class="mt-6 max-w-xl text-lg leading-8 text-[var(--trebbia-muted)]">
                            TREBBIA organiza citas desde WhatsApp, enlace público, recepción y otros canales en una agenda simple para negocios que quieren respirar mejor.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a class="inline-flex min-h-12 items-center justify-center rounded-md bg-[var(--trebbia-petrol)] px-6 text-base font-medium text-white hover:bg-[var(--trebbia-petrol-dark)]" href="{{ route('register') }}">Crear cuenta</a>
                            <a class="inline-flex min-h-12 items-center justify-center rounded-md border border-[var(--trebbia-line)] bg-white px-6 text-base font-medium text-[var(--trebbia-petrol)] hover:border-[var(--trebbia-aqua)]" href="#funciona">Ver cómo funciona</a>
                        </div>
                    </div>

                    <figure class="trebbia-commercial-card p-4">
                        <div class="rounded-2xl bg-[var(--trebbia-surface)] p-4">
                            <div class="flex flex-col gap-4 border-b border-[var(--trebbia-line)] pb-5 sm:flex-row sm:items-center sm:justify-between">
                                <div>
                                    <p class="text-sm font-medium text-[var(--trebbia-muted)]">Agenda de hoy</p>
                                    <h2 class="mt-1 text-2xl font-semibold">18 reservas organizadas</h2>
                                </div>
                                <span class="rounded-full bg-[var(--trebbia-aqua-soft)] px-4 py-2 text-sm font-medium text-[var(--trebbia-petrol)]">4 canales activos</span>
                            </div>

                            <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_15rem]">
                                <div class="space-y-3">
                                    @foreach (['WhatsApp: valoración inicial', 'Enlace público: sesión reservada', 'Recepción: cambio de horario'] as $item)
                                        <div class="grid grid-cols-[4.5rem_1fr_auto] items-center gap-3 rounded-xl border border-[var(--trebbia-line)] bg-[var(--trebbia-bg-soft)] p-3">
                                            <span class="rounded-md bg-white px-2 py-2 text-center text-sm font-semibold text-[var(--trebbia-petrol)]">{{ 8 + $loop->index }}:00</span>
                                            <span class="text-sm font-medium text-[var(--trebbia-ink)]">{{ $item }}</span>
                                            <span class="h-3 w-3 rounded-full bg-[var(--trebbia-aqua)]"></span>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="rounded-2xl bg-[var(--trebbia-petrol)] p-4 text-white">
                                    <p class="text-sm font-medium text-white/80">Ocupación</p>
                                    <div class="mt-5 flex h-32 items-end gap-2">
                                        @foreach ([42, 74, 58, 88, 64] as $bar)
                                            <span class="w-full rounded-t-md bg-[var(--trebbia-aqua)]" style="height: {{ $bar }}%"></span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </figure>
                </div>
            </section>

            <section id="plataforma" class="bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.82fr_1.18fr] lg:items-center">
                    <div>
                        <p class="trebbia-commercial-kicker">La plataforma</p>
                        <h2 class="trebbia-commercial-title mt-4 text-3xl sm:text-4xl">Todo lo importante queda en su lugar.</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        @foreach (['Reserva', 'Agenda', 'Seguimiento'] as $item)
                            <article class="rounded-2xl border border-[var(--trebbia-line)] bg-[var(--trebbia-bg)] p-5">
                                <div class="mb-6 h-28 rounded-xl bg-[var(--trebbia-blue-soft)]">
                                    <div class="flex h-full items-end gap-2 p-4">
                                        <span class="h-10 w-full rounded-md bg-white"></span>
                                        <span class="h-16 w-full rounded-md bg-[var(--trebbia-aqua)]"></span>
                                        <span class="h-12 w-full rounded-md bg-[var(--trebbia-petrol)]"></span>
                                    </div>
                                </div>
                                <h3 class="text-xl font-semibold">{{ $item }}</h3>
                                <p class="mt-2 text-sm leading-6 text-[var(--trebbia-muted)]">Claridad para actuar sin revisar cinco lugares distintos.</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="canales" class="bg-[var(--trebbia-bg-soft)] px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="trebbia-commercial-kicker">Canales</p>
                        <h2 class="trebbia-commercial-title mt-4 text-3xl sm:text-4xl">Tus clientes llegan por muchos caminos. La operación vive en uno.</h2>
                    </div>
                    <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        @foreach ($channels as $channel)
                            <article class="rounded-2xl border border-[var(--trebbia-line)] bg-white p-5">
                                <div class="mb-5 flex h-32 items-end rounded-xl bg-[var(--trebbia-surface-muted)] p-3">
                                    <span class="h-12 w-12 rounded-full bg-[var(--trebbia-aqua)]"></span>
                                    <span class="ml-3 h-16 flex-1 rounded-xl bg-[var(--trebbia-blue-soft)]"></span>
                                </div>
                                <h3 class="text-xl font-semibold">{{ $channel['name'] }}</h3>
                                <p class="mt-3 text-sm leading-6 text-[var(--trebbia-muted)]">{{ $channel['copy'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="funciona" class="bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
                    <div>
                        <p class="trebbia-commercial-kicker">Así funciona</p>
                        <h2 class="trebbia-commercial-title mt-4 text-3xl sm:text-4xl">De solicitud a cita confirmada, sin ruido.</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        @foreach ($steps as $step)
                            <article class="rounded-2xl border border-[var(--trebbia-line)] bg-[var(--trebbia-bg)] p-5">
                                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--trebbia-aqua-soft)] text-sm font-semibold text-[var(--trebbia-petrol)]">{{ $loop->iteration }}</span>
                                <h3 class="mt-6 text-xl font-semibold">{{ $step['title'] }}</h3>
                                <p class="mt-3 text-sm leading-6 text-[var(--trebbia-muted)]">{{ $step['copy'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="beneficios" class="bg-[var(--trebbia-bg)] px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="trebbia-commercial-kicker">Beneficios</p>
                        <h2 class="trebbia-commercial-title mt-4 text-3xl sm:text-4xl">La agenda deja de sentirse como una preocupación diaria.</h2>
                    </div>
                    <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($benefits as $benefit)
                            <div class="rounded-2xl border border-[var(--trebbia-line)] bg-white p-5">
                                <span class="mb-5 block h-2 w-12 rounded-full bg-[var(--trebbia-aqua)]"></span>
                                <p class="text-lg font-medium">{{ $benefit }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="capacidades" class="bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
                    <div>
                        <p class="trebbia-commercial-kicker">Capacidades</p>
                        <h2 class="trebbia-commercial-title mt-4 text-3xl sm:text-4xl">Una operación completa, fácil de entender.</h2>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        @foreach ($capabilities as $capability)
                            <span class="rounded-full border border-[var(--trebbia-line)] bg-[var(--trebbia-bg-soft)] px-4 py-3 text-sm font-medium text-[var(--trebbia-ink)]">{{ $capability }}</span>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="planes" class="bg-[var(--trebbia-bg-soft)] px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="trebbia-commercial-kicker">Planes</p>
                        <h2 class="trebbia-commercial-title mt-4 text-3xl sm:text-4xl">Empieza simple. Escala con calma.</h2>
                    </div>
                    <div class="mt-10 grid gap-5 lg:grid-cols-3">
                        @foreach ($plans as $plan)
                            <article class="rounded-2xl border border-[var(--trebbia-line)] bg-white p-6">
                                <h3 class="text-3xl font-semibold">{{ $plan['name'] }}</h3>
                                <p class="mt-4 min-h-24 text-sm leading-6 text-[var(--trebbia-muted)]">{{ $plan['copy'] }}</p>
                                <a class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-md bg-[var(--trebbia-petrol)] px-4 text-sm font-medium text-white hover:bg-[var(--trebbia-petrol-dark)]" href="{{ route('register') }}">Empezar</a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>
        </main>

        <footer class="border-t border-[var(--trebbia-line)] bg-white px-5 py-10 lg:px-8">
            <div class="mx-auto flex max-w-7xl flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">
                <x-trebbia-logo class="w-64" />
                <div class="flex gap-3">
                    <a class="rounded-md border border-[var(--trebbia-line)] px-4 py-2 text-sm font-medium text-[var(--trebbia-petrol)] hover:border-[var(--trebbia-aqua)]" href="{{ route('login') }}">Entrar</a>
                    <a class="rounded-md bg-[var(--trebbia-petrol)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--trebbia-petrol-dark)]" href="{{ route('register') }}">Crear cuenta</a>
                </div>
            </div>
        </footer>
    </div>
@endsection
