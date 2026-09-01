@extends('layouts.onboarding')

@section('onboarding-content')
    <h1 class="text-2xl font-bold">Horarios</h1>
    <p class="mt-2 text-[#64716d]">Define un horario general inicial. Luego podremos llevarlo por sede y profesional.</p>
    <form method="POST" action="{{ route('onboarding.store', 'horarios') }}" class="mt-6 space-y-5">
        @csrf
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="trebbia-label" for="opens_at">Abre</label>
                <input class="trebbia-input" id="opens_at" type="time" name="opens_at" value="{{ old('opens_at', '08:00') }}" required>
            </div>
            <div>
                <label class="trebbia-label" for="closes_at">Cierra</label>
                <input class="trebbia-input" id="closes_at" type="time" name="closes_at" value="{{ old('closes_at', '18:00') }}" required>
            </div>
        </div>
        <div>
            <p class="trebbia-label">Dias laborales</p>
            <div class="grid gap-2 sm:grid-cols-7">
                @foreach ([1 => 'Lun', 2 => 'Mar', 3 => 'Mie', 4 => 'Jue', 5 => 'Vie', 6 => 'Sab', 7 => 'Dom'] as $value => $label)
                    <label class="flex items-center gap-2 rounded-md border border-[#d7ddd7] bg-white px-3 py-2 text-sm font-semibold">
                        <input type="checkbox" name="weekdays[]" value="{{ $value }}" @checked(in_array($value, old('weekdays', [1, 2, 3, 4, 5]), false))>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>
        <button class="trebbia-button">Continuar</button>
    </form>
@endsection
