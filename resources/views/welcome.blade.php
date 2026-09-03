@extends('layouts.auth')

@section('title', 'trebbia. | Centro Inteligente de Reservas y Agendamiento')

@section('content')
    @php
        $navItems = [
            ['label' => 'La plataforma', 'href' => '#plataforma'],
            ['label' => 'Canales', 'href' => '#canales'],
            ['label' => 'Asi funciona', 'href' => '#funciona'],
            ['label' => 'Beneficios', 'href' => '#beneficios'],
            ['label' => 'Capacidades', 'href' => '#capacidades'],
            ['label' => 'Planes', 'href' => '#planes'],
        ];

        $channels = [
            ['name' => 'WhatsApp', 'copy' => 'Registra solicitudes que nacen en conversaciones y conviertelas en citas claras.'],
            ['name' => 'Enlace publico', 'copy' => 'Comparte una pagina de reservas para que tus clientes elijan servicio, profesional y horario.'],
            ['name' => 'Recepcion', 'copy' => 'Crea citas manualmente cuando el cliente llama, visita tu local o escribe por otro canal.'],
            ['name' => 'Redes sociales', 'copy' => 'Ordena mensajes de Instagram, Facebook u otros puntos de contacto sin perder trazabilidad.'],
        ];

        $benefits = [
            'Menos citas perdidas entre chats, llamadas y notas sueltas.',
            'Agenda centralizada por servicio, profesional, recurso y sede.',
            'Clientes con historial para atender mejor en cada nueva visita.',
            'Equipo alineado con roles, disponibilidad y estado de cada reserva.',
            'Recordatorios y seguimiento para reducir ausencias y reprocesos.',
            'Reportes simples para entender como se mueve tu negocio.',
        ];

        $capabilities = [
            'Agenda inteligente',
            'Reservas por WhatsApp',
            'Reserva publica por enlace',
            'Clientes e historial',
            'Servicios y precios',
            'Profesionales y horarios',
            'Recursos reservables',
            'Automatizaciones',
            'Reportes operativos',
            'Roles de equipo',
            'Planes por negocio',
            'Operacion multiempresa',
        ];

        $plans = [
            ['name' => 'Starter', 'price' => 'Para iniciar', 'copy' => 'Agenda, clientes, servicios y enlace publico para negocios que quieren ordenar sus reservas.'],
            ['name' => 'Pro', 'price' => 'Para crecer', 'copy' => 'Automatizaciones, recordatorios, reportes y operacion con varios profesionales.'],
            ['name' => 'Business', 'price' => 'Para equipos', 'copy' => 'Roles internos, mas capacidad, control avanzado y soporte para operaciones con mayor volumen.'],
        ];
    @endphp

    <div class="min-h-screen bg-[#F8FAFC] text-[#0F172A]">
        <header class="sticky top-0 z-40 border-b border-slate-200/80 bg-white/[0.92] backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-5 py-4 lg:px-8">
                <a href="{{ route('home') }}" class="shrink-0" aria-label="trebbia">
                    <x-trebbia-logo class="w-40 sm:w-48" />
                </a>

                <nav class="hidden items-center gap-6 lg:flex" aria-label="Menu principal">
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
                <div class="absolute inset-0 opacity-45" aria-hidden="true">
                    <div class="absolute left-[52%] top-12 h-[28rem] w-[42rem] rounded-[2rem] border border-cyan-300/20 bg-slate-900 shadow-2xl shadow-black/30">
                        <div class="flex items-center gap-2 border-b border-white/10 px-5 py-4">
                            <span class="h-2.5 w-2.5 rounded-full bg-[#06B6D4]"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-[#3B82F6]"></span>
                            <span class="h-2.5 w-2.5 rounded-full bg-slate-500"></span>
                        </div>
                        <div class="grid grid-cols-[12rem_1fr] gap-0">
                            <div class="space-y-3 border-r border-white/10 p-5">
                                @foreach (['Agenda', 'Clientes', 'WhatsApp', 'Reportes'] as $item)
                                    <div class="rounded-md {{ $loop->first ? 'bg-white/[0.14]' : 'bg-white/[0.07]' }} px-3 py-3 text-xs font-bold text-white/80">{{ $item }}</div>
                                @endforeach
                            </div>
                            <div class="p-5">
                                <div class="mb-5 grid grid-cols-3 gap-3">
                                    <div class="rounded-md bg-cyan-400/15 p-4">
                                        <p class="text-xs text-cyan-100">Hoy</p>
                                        <p class="mt-2 text-2xl font-black">18</p>
                                    </div>
                                    <div class="rounded-md bg-blue-400/15 p-4">
                                        <p class="text-xs text-blue-100">Confirmadas</p>
                                        <p class="mt-2 text-2xl font-black">14</p>
                                    </div>
                                    <div class="rounded-md bg-white/10 p-4">
                                        <p class="text-xs text-slate-200">Canales</p>
                                        <p class="mt-2 text-2xl font-black">4</p>
                                    </div>
                                </div>
                                <div class="space-y-3">
                                    @foreach (['WhatsApp: solicitud de valoracion', 'Enlace publico: reserva confirmada', 'Recepcion: ajuste de horario'] as $item)
                                        <div class="flex items-center justify-between rounded-md border border-white/10 bg-white/[0.08] px-4 py-3">
                                            <span class="text-sm text-white/[0.82]">{{ $item }}</span>
                                            <span class="rounded-md bg-cyan-300/15 px-2 py-1 text-xs font-bold text-cyan-100">{{ 8 + $loop->index }}:00</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="relative mx-auto grid min-h-[82vh] max-w-7xl content-center px-5 py-20 lg:px-8">
                    <div class="max-w-3xl">
                        <p class="mb-5 inline-flex rounded-md border border-cyan-300/30 bg-cyan-300/10 px-3 py-2 text-sm font-bold text-cyan-100">
                            WhatsApp incluido, no WhatsApp exclusivo.
                        </p>
                        <h1 class="max-w-4xl text-5xl font-extrabold leading-[1.04] sm:text-6xl lg:text-7xl">
                            Centraliza tus reservas. Atiende mejor. Crece con orden.
                        </h1>
                        <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-200 sm:text-xl">
                            TREBBIA organiza citas desde WhatsApp, enlace publico, recepcion y otros canales en una sola agenda inteligente para negocios que quieren operar con claridad y profesionalismo.
                        </p>
                        <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                            <a class="inline-flex min-h-12 items-center justify-center rounded-md bg-[#06B6D4] px-6 text-base font-black text-[#0F172A] shadow-lg shadow-cyan-950/30 hover:bg-[#67E8F9]" href="{{ route('register') }}">Crear cuenta</a>
                            <a class="inline-flex min-h-12 items-center justify-center rounded-md border border-white/20 px-6 text-base font-black text-white hover:bg-white/10" href="#funciona">Ver como funciona</a>
                        </div>
                    </div>
                </div>
            </section>

            <section id="plataforma" class="border-b border-slate-200 bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
                    <div>
                        <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-[#3B82F6]">La plataforma</p>
                        <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Un centro inteligente para reservas multicanal.</h2>
                        <p class="mt-5 text-lg leading-8 text-slate-600">
                            TREBBIA conecta agenda, clientes, servicios, profesionales, recursos, automatizaciones y reportes para que cada reserva tenga contexto, responsable y seguimiento.
                        </p>
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach (['Agenda clara', 'Cliente centralizado', 'Equipo sincronizado', 'Operacion medible'] as $item)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-5">
                                <span class="mb-5 block h-1.5 w-12 rounded-full bg-[#06B6D4]"></span>
                                <h3 class="text-lg font-extrabold">{{ $item }}</h3>
                                <p class="mt-3 text-sm leading-6 text-slate-600">Informacion organizada para decidir rapido, atender mejor y evitar reservas duplicadas.</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="canales" class="bg-[#F8FAFC] px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-[#3B82F6]">Canales</p>
                        <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Tus reservas pueden llegar por distintos caminos. TREBBIA las ordena en uno solo.</h2>
                    </div>
                    <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        @foreach ($channels as $channel)
                            <article class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                                <h3 class="text-lg font-extrabold">{{ $channel['name'] }}</h3>
                                <p class="mt-3 text-sm leading-6 text-slate-600">{{ $channel['copy'] }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="funciona" class="bg-[#0F172A] px-5 py-16 text-white lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-cyan-200">Asi funciona</p>
                        <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">De solicitud a reserva organizada, sin perder control.</h2>
                    </div>
                    <div class="mt-10 grid gap-4 lg:grid-cols-4">
                        @foreach (['El cliente solicita o agenda una cita.', 'TREBBIA ayuda a validar disponibilidad.', 'Tu equipo confirma o gestiona la reserva.', 'Todo queda organizado con historial y seguimiento.'] as $step)
                            <article class="rounded-lg border border-white/10 bg-white/[0.07] p-5">
                                <span class="flex h-10 w-10 items-center justify-center rounded-md bg-[#06B6D4] text-lg font-black text-[#0F172A]">{{ $loop->iteration }}</span>
                                <p class="mt-5 text-lg font-bold leading-7">{{ $step }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="beneficios" class="bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto grid max-w-7xl gap-10 lg:grid-cols-[0.8fr_1.2fr]">
                    <div>
                        <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-[#3B82F6]">Beneficios</p>
                        <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Menos desorden. Mas claridad para vender, atender y crecer.</h2>
                    </div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($benefits as $benefit)
                            <div class="flex gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <span class="mt-1 h-3 w-3 shrink-0 rounded-full bg-[#06B6D4]"></span>
                                <p class="text-sm font-semibold leading-6 text-slate-700">{{ $benefit }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="capacidades" class="bg-[#F8FAFC] px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div class="max-w-2xl">
                            <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-[#3B82F6]">Capacidades</p>
                            <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Las piezas esenciales para operar reservas con calidad.</h2>
                        </div>
                        <p class="max-w-xl text-base leading-7 text-slate-600">Empieza con lo basico y activa nuevas capacidades a medida que tu negocio necesita mas control.</p>
                    </div>
                    <div class="mt-10 flex flex-wrap gap-3">
                        @foreach ($capabilities as $capability)
                            <span class="rounded-md border border-slate-200 bg-white px-4 py-3 text-sm font-extrabold text-slate-700 shadow-sm">{{ $capability }}</span>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="planes" class="bg-white px-5 py-16 lg:px-8">
                <div class="mx-auto max-w-7xl">
                    <div class="max-w-2xl">
                        <p class="text-sm font-extrabold uppercase tracking-[0.28em] text-[#3B82F6]">Planes</p>
                        <h2 class="mt-4 text-3xl font-extrabold tracking-tight sm:text-4xl">Planes pensados para distintas etapas del negocio.</h2>
                    </div>
                    <div class="mt-10 grid gap-5 lg:grid-cols-3">
                        @foreach ($plans as $plan)
                            <article class="rounded-lg border border-slate-200 bg-[#F8FAFC] p-6">
                                <p class="text-sm font-extrabold uppercase tracking-[0.2em] text-[#64748B]">{{ $plan['price'] }}</p>
                                <h3 class="mt-4 text-2xl font-extrabold">{{ $plan['name'] }}</h3>
                                <p class="mt-4 min-h-24 text-sm leading-6 text-slate-600">{{ $plan['copy'] }}</p>
                                <a class="mt-6 inline-flex min-h-11 w-full items-center justify-center rounded-md bg-[#0F172A] px-4 text-sm font-black text-white hover:bg-[#1E293B]" href="{{ route('register') }}">Empezar</a>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-[#0F172A] px-5 py-16 text-white lg:px-8">
                <div class="mx-auto flex max-w-7xl flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <h2 class="text-3xl font-extrabold tracking-tight">Empieza a ordenar tus reservas con TREBBIA.</h2>
                        <p class="mt-3 max-w-2xl text-slate-300">Centraliza tu operacion, atiende mejor por todos tus canales y convierte cada cita en informacion util para crecer.</p>
                    </div>
                    <a class="inline-flex min-h-12 items-center justify-center rounded-md bg-[#06B6D4] px-6 text-base font-black text-[#0F172A] hover:bg-[#67E8F9]" href="{{ route('register') }}">Crear cuenta</a>
                </div>
            </section>
        </main>
    </div>
@endsection
