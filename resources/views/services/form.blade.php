@extends('layouts.app')

@section('title', ($service->exists ? 'Editar servicio' : 'Nuevo servicio').' | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', $service->exists ? 'Editar servicio' : 'Nuevo servicio')

@section('content')
    <div class="trebbia-card max-w-3xl p-6">
        @include('partials.errors')
        <form method="POST" action="{{ $service->exists ? route('servicios.update', $service) : route('servicios.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            @if ($service->exists)
                @method('PUT')
            @endif
            <div class="sm:col-span-2">
                <label class="trebbia-label" for="name">Nombre</label>
                <input class="trebbia-input" id="name" name="name" value="{{ old('name', $service->name) }}" required>
            </div>
            <div>
                <label class="trebbia-label" for="duration_minutes">Duracion en minutos</label>
                <input class="trebbia-input" id="duration_minutes" type="number" min="10" name="duration_minutes" value="{{ old('duration_minutes', $service->duration_minutes ?? 60) }}" required>
            </div>
            <div>
                <label class="trebbia-label" for="price">Precio</label>
                <input class="trebbia-input" id="price" type="number" min="0" step="0.01" name="price" value="{{ old('price', $service->exists ? $service->price_cents / 100 : 0) }}">
            </div>
            <div class="sm:col-span-2">
                <label class="trebbia-label" for="description">Descripcion</label>
                <textarea class="trebbia-input" id="description" name="description" rows="4">{{ old('description', $service->description) }}</textarea>
            </div>
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm font-semibold text-[#53615d] sm:col-span-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $service->exists ? $service->is_active : true))>
                Servicio activo
            </label>
            <div class="flex gap-3 sm:col-span-2">
                <button class="trebbia-button">Guardar</button>
                <a class="trebbia-button trebbia-button-secondary" href="{{ route('servicios.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
