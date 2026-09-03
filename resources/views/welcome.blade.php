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

        $productCards = [
            ['title' => 'Visualiza tu día de forma clara.', 'copy' => 'Consulta citas, profesionales, horarios disponibles y próximos servicios desde una agenda central.', 'image' => 'marketing/trebbia-agenda.svg', 'alt' => 'Módulo Agenda de TREBBIA'],
            ['title' => 'Toda la información de tus clientes.', 'copy' => 'Mantén datos, contacto e historial listos para cada atención.', 'image' => 'marketing/trebbia-clientes.svg', 'alt' => 'Módulo Clientes de TREBBIA'],
            ['title' => 'Organiza tu equipo y disponibilidad.', 'copy' => 'Configura profesionales, servicios, horarios y recursos sin complicarte.', 'image' => 'marketing/trebbia-equipo.svg', 'alt' => 'Módulo Profesionales de TREBBIA'],
        ];

        $steps = [
            ['title' => 'Configura tu negocio', 'copy' => 'Servicios, profesionales, recursos y horarios.', 'image' => 'marketing/trebbia-configuracion.svg'],
            ['title' => 'Organiza tu agenda', 'copy' => 'Centraliza citas, disponibilidad y responsables.', 'image' => 'marketing/trebbia-agenda.svg'],
            ['title' => 'Recibe reservas', 'copy' => 'Gestiona solicitudes desde TREBBIA, WhatsApp y enlace público.', 'image' => 'marketing/trebbia-dashboard.svg'],
        ];
    @endphp

    <div class="min-h-screen bg-[var(--trebbia-bg)] text-[var(--trebbia-ink)]">
        <header class="sticky top-0 z-40 border-b border-[var(--trebbia-line)] bg-[rgba(251,250,247,0.9)] backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-6 lg:px-8">
                <a href="{{ route('home') }}" class="shrink-0" aria-label="trebbia">
                    <x-trebbia-logo class="w-72 sm:w-88 lg:w-96" />
                </a>

                <nav class="hidden items-center gap-7 xl:flex" aria-label="Menú principal">
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
            <section class="overflow-hidden px-5 pb-14 pt-8 sm:pt-10 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-12 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                    <div>
                        <x-trebbia-logo class="mb-7 w-full max-w-[34rem]" />
                        <p class="trebbia-commercial-kicker">Reservas multicanal</p>
                        <h1 class="trebbia-commercial-title mt-5 max-w-3xl text-4xl sm:text-5xl lg:text-6xl">
                            Tu agenda, tu equipo y tus clientes. Todo en un solo lugar.
                        </h1>
                        <p class="mt-6 max-w-xl text-lg leading-8 text-[var(--trebbia-muted)]">
                            TREBBIA centraliza reservas, clientes, profesionales, servicios y recursos para que puedas dedicar menos tiempo a organizar y más tiempo a tu negocio.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            <a class="inline-flex min-h-12 items-center justify-center rounded-md bg-[var(--trebbia-petrol)] px-6 text-base font-medium text-white hover:bg-[var(--trebbia-petrol-dark)]" href="{{ route('register') }}">Crear mi cuenta</a>
                            <a class="inline-flex min-h-12 items-center justify-center rounded-md border border-[var(--trebbia-line)] bg-white px-6 text-base font-medium text-[var(--trebbia-petrol)] hover:border-[var(--trebbia-aqua)]" href="#funciona">Ver cómo funciona</a>
                        </div>
                    </div>

                    <figure class="trebbia-commercial-card p-4">
                        <div class="overflow-hidden rounded-2xl border border-[var(--trebbia-line)] bg-white">
                            <div class="flex items-center gap-2 border-b border-[var(--trebbia-line)] bg-[var(--trebbia-bg-soft)] px-4 py-3">
                                <span class="h-2.5 w-2.5 rounded-full bg-[var(--trebbia-aqua)]"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-[var(--trebbia-blue-soft)]"></span>
                                <span class="h-2.5 w-2.5 rounded-full bg-[var(--trebbia-petrol)]"></span>
                            </div>
                            <img class="block w-full" src="{{ asset('marketing/trebbia-dashboard.svg') }}" alt="Dashboard de TREBBIA con reservas, clientes y canales activos">
                        </div>
                    </figure>
                </div>
            </section>

            <section id="plataforma" class="bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="trebbia-commercial-kicker">La plataforma</p>
                        <h2 class="trebbia-commercial-title mt-4 text-3xl sm:text-4xl">Todo tu negocio, organizado en un solo lugar.</h2>
                        <p class="mt-4 text-base leading-7 text-[var(--trebbia-muted)]">TREBBIA conecta tu agenda, clientes, equipo y servicios en una plataforma sencilla de utilizar.</p>
                    </div>

                    <div class="mt-10 grid gap-6">
                        <article class="grid gap-6 rounded-3xl border border-[var(--trebbia-line)] bg-[var(--trebbia-bg)] p-4 lg:grid-cols-[1.05fr_0.95fr] lg:items-center">
                            <div class="overflow-hidden rounded-2xl border border-[var(--trebbia-line)] bg-white shadow-sm">
                                <img class="block w-full" src="{{ asset($productCards[0]['image']) }}" alt="{{ $productCards[0]['alt'] }}">
                            </div>
                            <div class="p-3 lg:p-8">
                                <h3 class="text-2xl font-semibold">{{ $productCards[0]['title'] }}</h3>
                                <p class="mt-3 text-base leading-7 text-[var(--trebbia-muted)]">{{ $productCards[0]['copy'] }}</p>
                            </div>
                        </article>

                        <div class="grid gap-6 lg:grid-cols-2">
                            @foreach (array_slice($productCards, 1) as $card)
                                <article class="rounded-3xl border border-[var(--trebbia-line)] bg-white p-4">
                                    <div class="overflow-hidden rounded-2xl border border-[var(--trebbia-line)] bg-white shadow-sm">
                                        <img class="block w-full" src="{{ asset($card['image']) }}" alt="{{ $card['alt'] }}">
                                    </div>
                                    <div class="p-3 pt-6">
                                        <h3 class="text-2xl font-semibold">{{ $card['title'] }}</h3>
                                        <p class="mt-3 text-base leading-7 text-[var(--trebbia-muted)]">{{ $card['copy'] }}</p>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="bg-[var(--trebbia-bg)] px-5 py-16 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                    <div>
                        <p class="trebbia-commercial-kicker">Producto real</p>
                        <h2 class="trebbia-commercial-title mt-4 text-3xl sm:text-4xl">La plataforma para organizar y automatizar negocios que trabajan con citas.</h2>
                    </div>
                    <div class="grid gap-4 md:grid-cols-3">
                        @foreach (['Reservas', 'Clientes', 'Equipo'] as $item)
                            <article class="rounded-2xl border border-[var(--trebbia-line)] bg-white p-5">
                                <div class="mb-5 h-24 rounded-xl bg-[var(--trebbia-bg-soft)] p-4">
                                    <div class="h-full rounded-lg bg-[var(--trebbia-aqua-soft)]"></div>
                                </div>
                                <h3 class="text-xl font-semibold">{{ $item }}</h3>
                                <p class="mt-2 text-sm leading-6 text-[var(--trebbia-muted)]">Módulos conectados para que cada cita tenga contexto y seguimiento.</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="funciona" class="bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="trebbia-commercial-kicker">Así funciona</p>
                        <h2 class="trebbia-commercial-title mt-4 text-3xl sm:text-4xl">Tres pasos para pasar del desorden al control.</h2>
                    </div>
                    <div class="mt-10 grid gap-5 lg:grid-cols-3">
                        @foreach ($steps as $step)
                            <article class="rounded-3xl border border-[var(--trebbia-line)] bg-[var(--trebbia-bg)] p-5">
                                <div class="mb-6 overflow-hidden rounded-2xl border border-[var(--trebbia-line)] bg-white">
                                    <img class="block w-full" src="{{ asset($step['image']) }}" alt="{{ $step['title'] }} en TREBBIA">
                                </div>
                                <span class="flex h-11 w-11 items-center justify-center rounded-full bg-[var(--trebbia-aqua-soft)] text-sm font-semibold text-[var(--trebbia-petrol)]">{{ $loop->iteration }}</span>
                                <h3 class="mt-5 text-xl font-semibold">{{ $step['title'] }}</h3>
                                <p class="mt-3 text-sm leading-6 text-[var(--trebbia-muted)]">{{ $step['copy'] }}</p>
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
                <x-trebbia-logo class="w-72 sm:w-80" />
                <div class="flex gap-3">
                    <a class="rounded-md border border-[var(--trebbia-line)] px-4 py-2 text-sm font-medium text-[var(--trebbia-petrol)] hover:border-[var(--trebbia-aqua)]" href="{{ route('login') }}">Entrar</a>
                    <a class="rounded-md bg-[var(--trebbia-petrol)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--trebbia-petrol-dark)]" href="{{ route('register') }}">Crear cuenta</a>
                </div>
            </div>
        </footer>
    </div>
@endsection
