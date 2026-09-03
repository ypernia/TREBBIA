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
            <div class="sm:col-span-2">
                <p class="trebbia-label">Servicios que presta</p>
                <div class="grid gap-2 rounded-md border border-[#d7ddd7] bg-white p-3 sm:grid-cols-2">
                    @forelse ($services as $service)
                        <label class="flex items-center gap-2 text-sm font-semibold text-[#53615d]">
                            <input type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, old('service_ids', $professional->exists ? $professional->services->pluck('id')->all() : [])))>
                            {{ $service->name }}
                        </label>
                    @empty
                        <p class="text-sm text-[#64716d]">Aun no tienes servicios activos.</p>
                    @endforelse
                </div>
            </div>
            <div class="sm:col-span-2">
                <p class="trebbia-label">Horario propio</p>
                <div class="overflow-hidden rounded-md border border-[#d7ddd7] bg-white">
                    @foreach ($weekdays as $weekday => $label)
                        @php
                            $schedule = $schedules->get($weekday);
                            $isClosed = (bool) old("schedule.$weekday.is_closed", $schedule?->is_closed ?? false);
                        @endphp
                        <div class="grid gap-3 border-b border-[#e7ebe7] p-3 md:grid-cols-[8rem_1fr_1fr_8rem] md:items-center">
                            <p class="font-semibold">{{ $label }}</p>
                            <div>
                                <label class="sr-only" for="schedule_{{ $weekday }}_starts_at">Inicio {{ $label }}</label>
                                <input class="trebbia-input" id="schedule_{{ $weekday }}_starts_at" type="time" name="schedule[{{ $weekday }}][starts_at]" value="{{ old("schedule.$weekday.starts_at", $schedule?->starts_at ?? '08:00') }}">
                            </div>
                            <div>
                                <label class="sr-only" for="schedule_{{ $weekday }}_ends_at">Fin {{ $label }}</label>
                                <input class="trebbia-input" id="schedule_{{ $weekday }}_ends_at" type="time" name="schedule[{{ $weekday }}][ends_at]" value="{{ old("schedule.$weekday.ends_at", $schedule?->ends_at ?? '18:00') }}">
                            </div>
                            <label class="flex items-center gap-2 text-sm font-semibold text-[#53615d]">
                                <input type="hidden" name="schedule[{{ $weekday }}][is_closed]" value="0">
                                <input type="checkbox" name="schedule[{{ $weekday }}][is_closed]" value="1" @checked($isClosed)>
                                Cerrado
                            </label>
                        </div>
                    @endforeach
                </div>
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
