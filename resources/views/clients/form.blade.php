@extends('layouts.app')

@section('title', ($client->exists ? 'Editar cliente' : 'Nuevo cliente').' | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', $client->exists ? 'Editar cliente' : 'Nuevo cliente')

@section('content')
    <div class="trebbia-card max-w-3xl p-6">
        @include('partials.errors')
        <form method="POST" action="{{ $client->exists ? route('clientes.update', $client) : route('clientes.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            @if ($client->exists)
                @method('PUT')
            @endif
            <div class="sm:col-span-2">
                <label class="trebbia-label" for="name">Nombre</label>
                <input class="trebbia-input" id="name" name="name" value="{{ old('name', $client->name) }}" required>
            </div>
            <div>
                <label class="trebbia-label" for="email">Correo</label>
                <input class="trebbia-input" id="email" type="email" name="email" value="{{ old('email', $client->email) }}">
            </div>
            <div>
                <label class="trebbia-label" for="phone">Telefono</label>
                <input class="trebbia-input" id="phone" name="phone" value="{{ old('phone', $client->phone) }}">
            </div>
            <div>
                <label class="trebbia-label" for="document_number">Documento</label>
                <input class="trebbia-input" id="document_number" name="document_number" value="{{ old('document_number', $client->document_number) }}">
            </div>
            <div>
                <label class="trebbia-label" for="birthdate">Fecha de nacimiento</label>
                <input class="trebbia-input" id="birthdate" type="date" name="birthdate" value="{{ old('birthdate', $client->birthdate?->format('Y-m-d')) }}">
            </div>
            <div class="flex items-center gap-3 rounded-md border border-[#d7ddd7] bg-white px-4 py-3">
                <input type="hidden" name="is_active" value="0">
                <input class="size-4" id="is_active" type="checkbox" name="is_active" value="1" @checked(old('is_active', $client->exists ? $client->is_active : true))>
                <label class="text-sm font-bold text-[#33413d]" for="is_active">Cliente activo</label>
            </div>
            <div class="sm:col-span-2">
                <label class="trebbia-label" for="notes">Notas</label>
                <textarea class="trebbia-input" id="notes" name="notes" rows="4">{{ old('notes', $client->notes) }}</textarea>
            </div>
            <div class="flex gap-3 sm:col-span-2">
                <button class="trebbia-button">Guardar</button>
                <a class="trebbia-button trebbia-button-secondary" href="{{ route('clientes.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
