@extends('layouts.app')

@section('title', 'Horarios | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Configuracion')

@section('content')
    <div class="trebbia-card max-w-5xl p-6">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h2 class="text-xl font-bold">Horarios del negocio</h2>
                <p class="mt-2 max-w-2xl text-sm text-[#64716d]">Define la disponibilidad general. La agenda avanzada usara estos horarios junto con servicios, profesionales, recursos y bloqueos.</p>
            </div>
        </div>
        @include('partials.errors')
        <form method="POST" action="{{ route('schedules.update') }}" class="mt-6 space-y-3">
            @csrf
            @method('PUT')
            @foreach ($weekdays as $weekday => $label)
                @php $schedule = $schedules->get($weekday); @endphp
                <div class="grid gap-3 rounded-md border border-[#e1e6e0] bg-white p-4 md:grid-cols-[10rem_1fr_1fr_8rem] md:items-center">
                    <p class="font-bold">{{ $label }}</p>
                    <div>
                        <label class="trebbia-label" for="schedule_{{ $weekday }}_opens_at">Abre</label>
                        <input class="trebbia-input" id="schedule_{{ $weekday }}_opens_at" type="time" name="schedule[{{ $weekday }}][opens_at]" value="{{ old("schedule.{$weekday}.opens_at", $schedule?->opens_at ?? '08:00') }}">
                    </div>
                    <div>
                        <label class="trebbia-label" for="schedule_{{ $weekday }}_closes_at">Cierra</label>
                        <input class="trebbia-input" id="schedule_{{ $weekday }}_closes_at" type="time" name="schedule[{{ $weekday }}][closes_at]" value="{{ old("schedule.{$weekday}.closes_at", $schedule?->closes_at ?? '18:00') }}">
                    </div>
                    <label class="flex items-center gap-2 text-sm font-semibold text-[#53615d]">
                        <input type="hidden" name="schedule[{{ $weekday }}][is_closed]" value="0">
                        <input type="checkbox" name="schedule[{{ $weekday }}][is_closed]" value="1" @checked(old("schedule.{$weekday}.is_closed", $schedule?->is_closed ?? $weekday > 5))>
                        Cerrado
                    </label>
                </div>
            @endforeach
            <button class="trebbia-button">Guardar horarios</button>
        </form>
    </div>
@endsection
