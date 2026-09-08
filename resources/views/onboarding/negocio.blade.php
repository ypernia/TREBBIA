@extends('layouts.onboarding')

@section('onboarding-content')
    <h1 class="text-2xl font-bold">Informacion del negocio</h1>
    <p class="mt-2 text-[#64716d]">Confirma los datos basicos que veras dentro de TREBBIA.</p>
    <form method="POST" action="{{ route('onboarding.store', 'negocio') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
        @csrf
        <div class="sm:col-span-2">
            <label class="trebbia-label" for="name">Nombre</label>
            <input class="trebbia-input" id="name" name="name" value="{{ old('name', $business->name) }}" required>
        </div>
        <x-industry-select :value="$business->industry" label="Tipo" />
        <div>
            <label class="trebbia-label" for="phone">Telefono</label>
            <input class="trebbia-input" id="phone" name="phone" value="{{ old('phone', $business->phone) }}">
        </div>
        <div class="sm:col-span-2">
            <label class="trebbia-label" for="email">Correo</label>
            <input class="trebbia-input" id="email" type="email" name="email" value="{{ old('email', $business->email) }}">
        </div>
        <div class="sm:col-span-2">
            <button class="trebbia-button">Continuar</button>
        </div>
    </form>
@endsection
