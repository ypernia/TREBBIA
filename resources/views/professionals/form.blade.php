@extends('layouts.app')

@section('title', ($professional->exists ? 'Editar profesional' : 'Nuevo profesional').' | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', $professional->exists ? 'Editar profesional' : 'Nuevo profesional')

@section('content')
    <div class="trebbia-card max-w-3xl p-6">
        @include('partials.errors')
        <form method="POST" action="{{ $professional->exists ? route('profesionales.update', $professional) : route('profesionales.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            @if ($professional->exists)
                @method('PUT')
            @endif
            <div class="sm:col-span-2">
                <label class="trebbia-label" for="name">Nombre</label>
                <input class="trebbia-input" id="name" name="name" value="{{ old('name', $professional->name) }}" required>
            </div>
            <div>
                <label class="trebbia-label" for="title">Especialidad</label>
                <input class="trebbia-input" id="title" name="title" value="{{ old('title', $professional->title) }}">
            </div>
            <div>
                <label class="trebbia-label" for="branch_id">Sede</label>
                <select class="trebbia-input" id="branch_id" name="branch_id">
                    <option value="">Sin sede</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) old('branch_id', $professional->branch_id) === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="trebbia-label" for="email">Correo</label>
                <input class="trebbia-input" id="email" type="email" name="email" value="{{ old('email', $professional->email) }}">
            </div>
            <div>
                <label class="trebbia-label" for="phone">Telefono</label>
                <input class="trebbia-input" id="phone" name="phone" value="{{ old('phone', $professional->phone) }}">
            </div>
            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm font-semibold text-[#53615d] sm:col-span-2">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $professional->exists ? $professional->is_active : true))>
                Profesional activo
            </label>
            <div class="flex gap-3 sm:col-span-2">
                <button class="trebbia-button">Guardar</button>
                <a class="trebbia-button trebbia-button-secondary" href="{{ route('profesionales.index') }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
