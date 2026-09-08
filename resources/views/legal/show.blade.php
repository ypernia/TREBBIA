@extends('layouts.auth')

@section('title', $title.' | TREBBIA')

@section('content')
    <div class="min-h-screen bg-[var(--trebbia-bg)] text-[var(--trebbia-ink)]">
        <header class="border-b border-[var(--trebbia-line)] bg-white px-5 py-6 lg:px-8">
            <div class="mx-auto flex max-w-5xl flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('home') }}" aria-label="Volver a TREBBIA">
                    <x-trebbia-logo class="w-64" />
                </a>
                <div class="flex gap-3">
                    <a class="rounded-md border border-[var(--trebbia-line)] px-4 py-2 text-sm font-medium text-[var(--trebbia-petrol)] hover:border-[var(--trebbia-aqua)]" href="{{ route('login') }}">Entrar</a>
                    <a class="rounded-md bg-[var(--trebbia-petrol)] px-4 py-2 text-sm font-medium text-white hover:bg-[var(--trebbia-petrol-dark)]" href="{{ route('register') }}">Crear cuenta</a>
                </div>
            </div>
        </header>

        <main class="px-5 py-12 lg:px-8">
            <article class="mx-auto max-w-5xl">
                <p class="trebbia-commercial-kicker">Confianza TREBBIA</p>
                <h1 class="trebbia-commercial-title mt-4 text-3xl sm:text-5xl">{{ $title }}</h1>
                <p class="mt-5 max-w-3xl text-base leading-7 text-[var(--trebbia-muted)]">{{ $intro }}</p>
                <p class="mt-4 text-sm font-semibold text-[var(--trebbia-muted)]">Última actualización: {{ $updatedAt }}</p>

                <div class="mt-10 grid gap-5">
                    @foreach ($sections as $section)
                        <section class="rounded-2xl border border-[var(--trebbia-line)] bg-white p-6">
                            <h2 class="text-xl font-semibold">{{ $section['title'] }}</h2>
                            <p class="mt-3 text-sm leading-7 text-[var(--trebbia-muted)]">{{ $section['body'] }}</p>
                        </section>
                    @endforeach
                </div>

                <section class="mt-8 rounded-2xl border border-[var(--trebbia-line)] bg-[var(--trebbia-bg-soft)] p-6">
                    <h2 class="text-xl font-semibold">Nota importante</h2>
                    <p class="mt-3 text-sm leading-7 text-[var(--trebbia-muted)]">
                        Este contenido es una base informativa y comercial. Puede requerir revisión legal según el país, la industria y las políticas particulares de cada negocio.
                    </p>
                </section>
            </article>
        </main>

        @include('partials.marketing-footer')
    </div>
@endsection
