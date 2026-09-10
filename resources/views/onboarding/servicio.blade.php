@extends('layouts.onboarding')

@section('onboarding-content')
    @php
        $selectedPriceType = old('price_type', \App\Models\Service::PRICE_FIXED);
    @endphp
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
            <label class="trebbia-label" for="price_type">Tipo de precio</label>
            <select class="trebbia-input" id="price_type" name="price_type" required>
                @foreach (\App\Models\Service::priceTypeLabels() as $value => $label)
                    <option value="{{ $value }}" @selected($selectedPriceType === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div data-price-field="min">
            <label class="trebbia-label" for="price">Precio inicial</label>
            <input class="trebbia-input" id="price" type="number" min="0" step="0.01" name="price" value="{{ old('price', 0) }}">
        </div>
        <div data-price-field="max">
            <label class="trebbia-label" for="price_max">Precio maximo</label>
            <input class="trebbia-input" id="price_max" type="number" min="0" step="0.01" name="price_max" value="{{ old('price_max') }}">
        </div>
        <div class="sm:col-span-2">
            <label class="trebbia-label" for="description">Descripcion</label>
            <textarea class="trebbia-input" id="description" name="description" rows="3">{{ old('description') }}</textarea>
        </div>
        <div class="sm:col-span-2">
            <button class="trebbia-button">Continuar</button>
        </div>
    </form>
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
