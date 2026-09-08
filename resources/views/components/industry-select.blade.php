@props([
    'value' => null,
    'label' => 'Tipo de negocio',
])

@php
    $options = \App\Support\BusinessIndustries::options();
    $selected = old('industry', \App\Support\BusinessIndustries::selectedValue($value));
    $otherValue = old('industry_other', $selected === \App\Support\BusinessIndustries::OTHER ? $value : '');
    $uid = 'industry_'.uniqid();
@endphp

<div>
    <label class="trebbia-label" for="{{ $uid }}">{{ $label }}</label>
    <select class="trebbia-input" id="{{ $uid }}" name="industry" data-industry-select required>
        <option value="">Selecciona una opcion</option>
        @foreach ($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" @selected($selected === $optionValue)>{{ $optionLabel }}</option>
        @endforeach
        <option value="{{ \App\Support\BusinessIndustries::OTHER }}" @selected($selected === \App\Support\BusinessIndustries::OTHER)>Otro</option>
    </select>

    <div class="mt-3 {{ $selected === \App\Support\BusinessIndustries::OTHER ? '' : 'hidden' }}" data-industry-other-wrapper>
        <label class="trebbia-label" for="{{ $uid }}_other">Especifica el tipo de negocio</label>
        <input class="trebbia-input" id="{{ $uid }}_other" name="industry_other" value="{{ $otherValue }}" placeholder="Ej: fotografia, coworking, consultoria...">
    </div>
</div>

@once
    <script>
        document.addEventListener('change', function (event) {
            if (!event.target.matches('[data-industry-select]')) {
                return;
            }

            const wrapper = event.target.closest('div').querySelector('[data-industry-other-wrapper]');

            if (!wrapper) {
                return;
            }

            wrapper.classList.toggle('hidden', event.target.value !== '{{ \App\Support\BusinessIndustries::OTHER }}');
        });
    </script>
@endonce
