@extends('layouts.onboarding')

@section('onboarding-content')
    <h1 class="text-2xl font-bold">Primer servicio</h1>
    <p class="mt-2 text-[#64716d]">Crea un servicio base para validar el catalogo de reservas.</p>
    <form method="POST" action="{{ route('onboarding.store', 'servicio') }}" class="mt-6 grid gap-4 sm:grid-cols-2">
        @csrf
        <div class="sm:col-span-2">
            <label class="trebbia-label" for="name">Nombre del servicio</label>
            <input class="trebbia-input" id="name" name="name" value="{{ old('name') }}" placeholder="Consulta inicial" required>
        </div>
        <div>
            <label class="trebbia-label" for="duration_minutes">Duracion</label>
            <input class="trebbia-input" id="duration_minutes" type="number" min="10" name="duration_minutes" value="{{ old('duration_minutes', 60) }}" required>
        </div>
        <div>
            <label class="trebbia-label" for="price">Precio</label>
            <input class="trebbia-input" id="price" type="number" min="0" step="0.01" name="price" value="{{ old('price', 0) }}">
        </div>
        <div class="sm:col-span-2">
            <label class="trebbia-label" for="description">Descripcion</label>
            <textarea class="trebbia-input" id="description" name="description" rows="3">{{ old('description') }}</textarea>
        </div>
        <div class="sm:col-span-2">
            <button class="trebbia-button">Continuar</button>
        </div>
    </form>
@endsection
