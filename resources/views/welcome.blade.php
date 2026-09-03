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
            ['name' => 'WhatsApp', 'tag' => 'Conversación', 'copy' => 'Solicitudes que nacen en chat y terminan como reservas ordenadas.'],
            ['name' => 'Enlace público', 'tag' => 'Autoservicio', 'copy' => 'Tus clientes eligen servicio, profesional y horario disponible.'],
            ['name' => 'Recepción', 'tag' => 'Equipo', 'copy' => 'Citas creadas por llamadas, visitas o mensajes directos.'],
            ['name' => 'Redes sociales', 'tag' => 'Origen', 'copy' => 'Mensajes de campañas y perfiles convertidos en seguimiento real.'],
        ];

        $benefits = [
            'Menos citas perdidas',
            'Agenda centralizada',
            'Clientes con historial',
            'Equipo alineado',
            'Recordatorios claros',
            'Reportes simples',
        ];

        $capabilities = [
            'Agenda inteligente',
            'WhatsApp',
            'Reservas públicas',
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
            ['name' => 'Starter', 'for' => 'Para ordenar', 'items' => ['Agenda', 'Clientes', 'Servicios', 'Link público']],
            ['name' => 'Pro', 'for' => 'Para crecer', 'items' => ['Automatizaciones', 'Recordatorios', 'Reportes', 'Más profesionales']],
            ['name' => 'Business', 'for' => 'Para equipos', 'items' => ['Roles', 'Mayor capacidad', 'Control avanzado', 'Operación multi-sede']],
        ];
    @endphp

    <div class="min-h-screen bg-[#F8FAFC] text-[#0F172A]">
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/[0.94] backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-4 lg:px-8">
                <a href="{{ route('home') }}" class="shrink-0" aria-label="trebbia">
                    <x-trebbia-logo class="w-52 sm:w-64" />
                </a>

                <nav class="hidden items-center gap-6 lg:flex" aria-label="Menú principal">
                    @foreach ($navItems as $item)
                        <a class="text-sm font-bold text-slate-600 hover:text-[#0F172A]" href="{{ $item['href'] }}">{{ $item['label'] }}</a>
                    @endforeach
                </nav>

                <div class="flex items-center gap-2">
                    <a class="hidden rounded-md px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100 sm:inline-flex" href="{{ route('login') }}">Entrar</a>
                    <a class="rounded-md bg-[#0F172A] px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-[#1E293B]" href="{{ route('register') }}">Crear cuenta</a>
                </div>
            </div>
        </header>

        <main>
            <section class="relative overflow-hidden bg-[#0F172A] text-white">
                <div class="mx-auto grid min-h-[84vh] max-w-7xl gap-12 px-5 py-16 lg:grid-cols-[0.92fr_1.08fr] lg:items-center lg:px-8">
                    <div>
                        <x-trebbia-logo class="mb-8 w-72 brightness-0 invert sm:w-96" />
                        <p class="mb-5 inline-flex rounded-md border border-cyan-300/30 bg-cyan-300/10 px-3 py-2 text-sm font-bold text-cyan-100">
                            WhatsApp incluido, no WhatsApp exclusivo.
                        </p>
                        <h1 class="max-w-4xl text-5xl font-black leading-[1.02] sm:text-6xl lg:text-7xl">
                            Centraliza tus reservas. Atiende mejor. Crece con orden.
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200">
                            TREBBIA organiza citas desde WhatsApp, enlace público, recepción y otros canales en una sola agenda inteligente.
                        </p>
                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a class="inline-flex min-h-12 items-center justify-center rounded-md bg-[#06B6D4] px-6 text-base font-black text-[#0F172A] shadow-lg shadow-cyan-950/30 hover:bg-[#67E8F9]" href="{{ route('register') }}">Crear cuenta</a>
                            <a class="inline-flex min-h-12 items-center justify-center rounded-md border border-white/20 px-6 text-base font-black text-white hover:bg-white/10" href="#funciona">Ver cómo funciona</a>
                        </div>
                    </div>

                    <div class="relative">
                        <div class="rounded-[2rem] border border-white/10 bg-white/[0.08] p-4 shadow-2xl shadow-black/30">
                            <div class="rounded-[1.35rem] bg-white p-4 text-[#0F172A]">
                                <div class="flex items-center justify-between border-b border-slate-200 pb-4">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-[0.24em] text-[#64748B]">Hoy</p>
                                        <h2 class="mt-1 text-2xl font-black">Agenda inteligente</h2>
                                    </div>
                                    <span class="rounded-md bg-cyan-100 px-3 py-2 text-sm font-black text-cyan-700">18 citas</span>
                                </div>
                                <div class="mt-5 grid gap-4 lg:grid-cols-[1fr_13rem]">
                                    <div class="space-y-3">
                                        @foreach (['WhatsApp: valoración inicial', 'Link público: sesión reservada', 'Recepción: cambio de horario'] as $item)
                                            <div class="grid grid-cols-[4rem_1fr_auto] items-center gap-3 rounded-lg border border-slate-200 bg-slate-50 p-3">
                                                <span class="rounded-md bg-[#0F172A] px-2 py-2 text-center text-sm font-black text-white">{{ 8 + $loop->index }}:00</span>
                                                <span class="text-sm font-bold text-slate-700">{{ $item }}</span>
                                                <span class="h-3 w-3 rounded-full bg-[#06B6D4]"></span>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div class="rounded-lg bg-[#0F172A] p-4 text-white">
                                        <p class="text-xs font-bold text-cyan-100">Canales activos</p>
                                        <div class="mt-5 grid grid-cols-2 gap-2">
                                            @foreach (['WA', 'Link', 'Recep.', 'Redes'] as $item)
                                                <div class="rounded-md bg-white/[0.1] p-3 text-center text-xs font-black">{{ $item }}</div>
                                            @endforeach
                                        </div>
                                        <div class="mt-5 h-24 rounded-md bg-gradient-to-br from-[#06B6D4] to-[#3B82F6]"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section id="plataforma" class="bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.28em] text-[#3B82F6]">La plataforma</p>
                        <h2 class="mt-4 text-4xl font-black tracking-tight">Todo lo que entra, queda organizado.</h2>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-3">
                        @foreach (['Reserva', 'Agenda', 'Seguimiento'] as $item)
                            <article class="rounded-xl border border-slate-200 bg-[#F8FAFC] p-5">
                                <div class="mb-6 h-28 rounded-lg bg-gradient-to-br from-slate-900 via-blue-900 to-cyan-500"></div>
                                <h3 class="text-xl font-black">{{ $item }}</h3>
                                <p class="mt-2 text-sm leading-6 text-slate-600">Información clara para que tu equipo actúe sin adivinar.</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="canales" class="bg-[#F8FAFC] px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="text-sm font-black uppercase tracking-[0.28em] text-[#3B82F6]">Canales</p>
                        <h2 class="mt-4 text-4xl font-black tracking-tight">Reservas desde donde tus clientes ya están.</h2>
                    </div>
                    <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        @foreach ($channels as $channel)
                            <article class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                                <div class="mb-5 flex h-32 items-end rounded-lg bg-[#0F172A] p-3">
                                    <span class="rounded-md bg-white px-3 py-2 text-sm font-black text-[#0F172A]">{{ $channel['tag'] }}</span>
                                </div>
                                <h3 class="text-xl font-black">{{ $channel['name'] }}</h3>
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $channel['copy'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="funciona" class="bg-[#0F172A] px-5 py-16 text-white lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.85fr_1.15fr] lg:items-center">
                    <div>
                        <p class="text-sm font-black uppercase tracking-[0.28em] text-cyan-200">Así funciona</p>
                        <h2 class="mt-4 text-4xl font-black tracking-tight">De solicitud a cita confirmada.</h2>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (['Solicitud', 'Disponibilidad', 'Confirmación', 'Historial'] as $step)
                            <article class="rounded-xl border border-white/10 bg-white/[0.07] p-5">
                                <span class="flex h-11 w-11 items-center justify-center rounded-md bg-[#06B6D4] text-lg font-black text-[#0F172A]">{{ $loop->iteration }}</span>
                                <h3 class="mt-5 text-xl font-black">{{ $step }}</h3>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="beneficios" class="bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="text-sm font-black uppercase tracking-[0.28em] text-[#3B82F6]">Beneficios</p>
                        <h2 class="mt-4 text-4xl font-black tracking-tight">Más claridad para vender, atender y crecer.</h2>
                    </div>
                    <div class="mt-10 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($benefits as $benefit)
                            <div class="rounded-xl border border-slate-200 bg-[#F8FAFC] p-5">
                                <span class="mb-5 block h-2 w-12 rounded-full bg-[#06B6D4]"></span>
                                <p class="text-lg font-black">{{ $benefit }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="capacidades" class="bg-[#F8FAFC] px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="grid gap-8 lg:grid-cols-[0.8fr_1.2fr] lg:items-center">
                        <div>
                            <p class="text-sm font-black uppercase tracking-[0.28em] text-[#3B82F6]">Capacidades</p>
                            <h2 class="mt-4 text-4xl font-black tracking-tight">Una operación completa, sin sentirse pesada.</h2>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @foreach ($capabilities as $capability)
                                <span class="rounded-md border border-slate-200 bg-white px-4 py-3 text-sm font-black text-slate-700 shadow-sm">{{ $capability }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section id="planes" class="bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="text-sm font-black uppercase tracking-[0.28em] text-[#3B82F6]">Planes</p>
                        <h2 class="mt-4 text-4xl font-black tracking-tight">Empieza simple. Escala cuando lo necesites.</h2>
                    </div>
                    <div class="mt-10 grid gap-5 lg:grid-cols-3">
                        @foreach ($plans as $plan)
                            <article class="rounded-xl border border-slate-200 bg-[#F8FAFC] p-6">
                                <p class="text-sm font-black uppercase tracking-[0.2em] text-[#64748B]">{{ $plan['for'] }}</p>
                                <h3 class="mt-4 text-3xl font-black">{{ $plan['name'] }}</h3>
                                <div class="mt-6 space-y-3">
                                    @foreach ($plan['items'] as $item)
                                        <p class="rounded-md bg-white px-3 py-2 text-sm font-bold text-slate-700">{{ $item }}</p>
                                    @endforeach
                                </div>
                                <a class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-md bg-[#0F172A] px-4 text-sm font-black text-white hover:bg-[#1E293B]" href="{{ route('register') }}">Empezar</a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-[#0F172A] px-5 py-16 text-white lg:px-8">
                <div class="mx-auto flex max-w-7xl flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-4xl font-black tracking-tight">Ordena tus reservas con TREBBIA.</h2>
                        <p class="mt-3 max-w-2xl text-slate-300">WhatsApp, enlace público, recepción y equipo en una sola agenda inteligente.</p>
                    </div>
                    <a class="inline-flex min-h-12 items-center justify-center rounded-md bg-[#06B6D4] px-6 text-base font-black text-[#0F172A] hover:bg-[#67E8F9]" href="{{ route('register') }}">Crear cuenta</a>
                </div>
            </section>
        </main>
    </div>
@endsection
