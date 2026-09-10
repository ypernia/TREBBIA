@extends('layouts.app')

@section('title', ($service->exists ? 'Editar servicio' : 'Nuevo servicio').' | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', $service->exists ? 'Editar servicio' : 'Nuevo servicio')

@section('content')
    @php
        $selectedPriceType = old('price_type', $service->price_type ?: \App\Models\Service::PRICE_FIXED);
    @endphp
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
                <label class="trebbia-label" for="price_type">Tipo de precio</label>
                <select class="trebbia-input" id="price_type" name="price_type" required>
                    @foreach (\App\Models\Service::priceTypeLabels() as $value => $label)
                        <option value="{{ $value }}" @selected($selectedPriceType === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div data-price-field="min">
                <label class="trebbia-label" for="price">Precio inicial</label>
                <input class="trebbia-input" id="price" type="number" min="0" step="0.01" name="price" value="{{ old('price', $service->exists ? $service->price_cents / 100 : 0) }}">
            </div>
            <div data-price-field="max">
                <label class="trebbia-label" for="price_max">Precio maximo</label>
                <input class="trebbia-input" id="price_max" type="number" min="0" step="0.01" name="price_max" value="{{ old('price_max', $service->exists ? $service->price_max_cents / 100 : null) }}">
            </div>
            <div class="sm:col-span-2">
                <label class="trebbia-label" for="description">Descripcion</label>
                <textarea class="trebbia-input" id="description" name="description" rows="4">{{ old('description', $service->description) }}</textarea>
            </div>
            <div class="sm:col-span-2">
                <p class="trebbia-label">Profesionales que prestan este servicio</p>
                <div class="grid gap-2 rounded-md border border-[#d7ddd7] bg-white p-3 sm:grid-cols-2">
                    @forelse ($professionals as $professional)
                        <label class="flex items-center gap-2 text-sm font-semibold text-[#53615d]">
                            <input type="checkbox" name="professional_ids[]" value="{{ $professional->id }}" @checked(in_array($professional->id, old('professional_ids', $service->exists ? $service->professionals->pluck('id')->all() : [])))>
                            {{ $professional->name }}
                        </label>
                    @empty
                        <p class="text-sm text-[#64716d]">Aun no tienes profesionales activos.</p>
                    @endforelse
                </div>
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
    <script>
        const priceType = document.getElementById('price_type');
        const minField = document.querySelector('[data-price-field="min"]');
        const maxField = document.querySelector('[data-price-field="max"]');
        const priceInput = document.getElementById('price');
        const maxInput = document.getElementById('price_max');

        function syncPriceFields() {
            const type = priceType.value;
            minField.classList.toggle('hidden', type === 'to_define');
            maxField.classList.toggle('hidden', type !== 'range');
            priceInput.required = type !== 'to_define';
            maxInput.required = type === 'range';
        }

        priceType.addEventListener('change', syncPriceFields);
        syncPriceFields();
    </script>
@endsection
