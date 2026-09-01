@extends('layouts.auth')

@section('title', 'TREBBIA')

@section('content')
    <div class="grid min-h-screen lg:grid-cols-[1.1fr_0.9fr]">
        <section class="flex items-center px-6 py-10 sm:px-10 lg:px-16">
            <div class="max-w-2xl">
                <div class="mb-8 inline-flex items-center gap-3 rounded-md border border-[#dce5df] bg-white px-3 py-2 text-sm font-semibold text-[#245f57]">
                    <span class="h-2 w-2 rounded-full bg-[#2f7d6d]"></span>
                    Base SaaS multiempresa para reservas online
                </div>
                <h1 class="text-4xl font-bold leading-tight text-[#18211f] sm:text-5xl">TREBBIA</h1>
                <p class="mt-5 max-w-xl text-lg leading-8 text-[#53615d]">
                    Organiza agendas, clientes, servicios y profesionales desde una plataforma sobria, segura y preparada para crecer por empresa.
                </p>
                <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                    <a class="trebbia-button" href="{{ route('register') }}">Crear cuenta</a>
                    <a class="trebbia-button trebbia-button-secondary" href="{{ route('login') }}">Iniciar sesion</a>
                </div>
            </div>
        </section>
        <section class="hidden border-l border-[#e1e6e0] bg-[#eaf1ed] p-10 lg:flex lg:items-center">
            <div class="w-full rounded-lg border border-[#d7ddd7] bg-white p-8 shadow-sm">
                <div class="flex items-center justify-between border-b border-[#e7ebe7] pb-5">
                    <div>
                        <p class="text-sm font-semibold text-[#64716d]">Hoy</p>
                        <p class="text-2xl font-bold text-[#18211f]">Agenda clara</p>
                    </div>
                    <span class="rounded-md bg-[#edf7f4] px-3 py-1 text-sm font-bold text-[#245f57]">Online</span>
                </div>
                <div class="mt-6 space-y-4">
                    @foreach (['Fisioterapia inicial', 'Corte y barba', 'Consulta estetica'] as $item)
                        <div class="flex items-center justify-between rounded-md border border-[#e1e6e0] p-4">
                            <div>
                                <p class="font-semibold text-[#18211f]">{{ $item }}</p>
                                <p class="text-sm text-[#64716d]">Profesional asignado</p>
                            </div>
                            <span class="text-sm font-bold text-[#245f57]">{{ 8 + $loop->index }}:00</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>
@endsection
