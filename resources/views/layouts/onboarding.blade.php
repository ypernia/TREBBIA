@extends('layouts.auth')

@section('title', 'Onboarding | TREBBIA')

@section('content')
    @php
        $labels = ['negocio' => 'Negocio', 'horarios' => 'Horarios', 'servicio' => 'Servicio', 'profesional' => 'Profesional', 'finalizar' => 'Finalizar'];
        $currentIndex = array_search($step, $steps, true);
    @endphp
    <div class="min-h-screen px-5 py-8">
        <div class="mx-auto max-w-4xl">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <a href="{{ route('dashboard') }}" class="text-2xl font-bold">TREBBIA</a>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $business->name }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57]">Salir</button>
                </form>
            </div>

            <div class="mt-8 grid gap-3 sm:grid-cols-5">
                @foreach ($steps as $item)
                    @php $index = array_search($item, $steps, true); @endphp
                    <div class="rounded-md border px-3 py-3 text-sm font-bold {{ $index <= $currentIndex ? 'border-[#b9d8cd] bg-[#edf7f4] text-[#245f57]' : 'border-[#e1e6e0] bg-white text-[#64716d]' }}">
                        {{ $index + 1 }}. {{ $labels[$item] }}
                    </div>
                @endforeach
            </div>

            <div class="trebbia-card mt-6 p-6 sm:p-8">
                @include('partials.errors')
                @yield('onboarding-content')
            </div>
        </div>
    </div>
@endsection
