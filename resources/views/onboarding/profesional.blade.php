@extends('layouts.onboarding')

@section('onboarding-content')
    <h1 class="text-2xl font-bold">Primer profesional</h1>
    <p class="mt-2 text-[#64716d]">Registra a la primera persona que prestara servicios.</p>
    <form method="POST" action="{{ route('onboarding.store', 'profesional') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
        @csrf
        <div class="sm:col-span-2">
            <label class="trebbia-label" for="name">Nombre</label>
            <input class="trebbia-input" id="name" name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label class="trebbia-label" for="title">Cargo o especialidad</label>
            <input class="trebbia-input" id="title" name="title" value="{{ old('title') }}">
        </div>
        <div>
            <label class="trebbia-label" for="phone">Telefono</label>
            <input class="trebbia-input" id="phone" name="phone" value="{{ old('phone') }}">
        </div>
        <div class="sm:col-span-2">
            <label class="trebbia-label" for="email">Correo</label>
            <input class="trebbia-input" id="email" type="email" name="email" value="{{ old('email') }}">
        </div>
        <div class="sm:col-span-2">
            <button class="trebbia-button">Continuar</button>
        </div>
    </form>
@endsection
