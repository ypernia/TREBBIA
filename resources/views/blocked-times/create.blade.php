@extends('layouts.app')

@section('title', 'Bloquear tiempo | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Bloquear tiempo')

@section('content')
    @php
        $mode = $attributes['mode'] ?? 'single';
        $returnDate = $attributes['date'] ?? $attributes['date_from'] ?? now($business->timezone)->toDateString();
        $selectedWeekdays = collect($attributes['weekdays'] ?? [])->map(fn ($weekday) => (string) $weekday)->all();
        $weekdayOptions = [
            1 => 'Lun',
            2 => 'Mar',
            3 => 'Mie',
            4 => 'Jue',
            5 => 'Vie',
            6 => 'Sab',
            7 => 'Dom',
        ];
    @endphp
    @include('partials.errors')

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <section class="trebbia-card p-6">
            <div class="flex flex-col gap-2 border-b border-[#e7ebe7] pb-5">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Disponibilidad</p>
                <h2 class="text-2xl font-bold">Revisar bloqueo</h2>
                <p class="max-w-2xl text-sm leading-6 text-[#64716d]">TREBBIA revisa las citas afectadas antes de guardar. El bloqueo no cambia citas existentes; solo evita nuevas reservas incompatibles.</p>
            </div>

            <form method="GET" action="{{ route('blocked-times.create') }}" class="mt-6 grid gap-4 md:grid-cols-2">
                <input type="hidden" name="preview" value="1">

                <div class="md:col-span-2">
                    <label class="trebbia-label" for="mode">Aplicar bloqueo</label>
                    <select class="trebbia-input" id="mode" name="mode" required>
                        <option value="single" @selected($mode === 'single')>Una fecha especifica</option>
                        <option value="multiple" @selected($mode === 'multiple')>Varios dias</option>
                    </select>
                </div>

                <div>
                    <label class="trebbia-label" for="scope">Tipo de bloqueo</label>
                    <select class="trebbia-input" id="scope" name="scope" required>
                        @foreach ($scopeOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($attributes['scope'] ?? 'business') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="professional-field">
                    <label class="trebbia-label" for="professional_id">Profesional</label>
                    <select class="trebbia-input" id="professional_id" name="professional_id">
                        <option value="">Seleccionar</option>
                        @foreach ($professionals as $professional)
                            <option value="{{ $professional->id }}" @selected((string) ($attributes['professional_id'] ?? '') === (string) $professional->id)>{{ $professional->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div id="resource-field">
                    <label class="trebbia-label" for="resource_id">Recurso</label>
                    <select class="trebbia-input" id="resource_id" name="resource_id">
                        <option value="">Seleccionar</option>
                        @foreach ($resources as $resource)
                            <option value="{{ $resource->id }}" @selected((string) ($attributes['resource_id'] ?? '') === (string) $resource->id)>{{ $resource->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div data-mode-field="single">
                    <label class="trebbia-label" for="date">Fecha</label>
                    <input class="trebbia-input" id="date" name="date" type="date" value="{{ $returnDate }}" required>
                </div>

                <div data-mode-field="multiple">
                    <label class="trebbia-label" for="date_from">Desde fecha</label>
                    <input class="trebbia-input" id="date_from" name="date_from" type="date" value="{{ $attributes['date_from'] ?? $returnDate }}">
                </div>

                <div data-mode-field="multiple">
                    <label class="trebbia-label" for="date_to">Hasta fecha</label>
                    <input class="trebbia-input" id="date_to" name="date_to" type="date" value="{{ $attributes['date_to'] ?? $returnDate }}">
                </div>

                <div class="md:col-span-2" data-mode-field="multiple">
                    <p class="trebbia-label">Dias</p>
                    <div class="grid gap-2 rounded-md border border-[#d7ddd7] bg-white p-3 sm:grid-cols-7">
                        @foreach ($weekdayOptions as $weekday => $label)
                            <label class="flex items-center justify-center gap-2 rounded-md border border-[#e1e6e0] px-3 py-2 text-sm font-bold text-[#53615d] has-[:checked]:border-[#245f57] has-[:checked]:bg-[#edf7f4] has-[:checked]:text-[#245f57]">
                                <input type="checkbox" name="weekdays[]" value="{{ $weekday }}" @checked(in_array((string) $weekday, $selectedWeekdays, true))>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="trebbia-label" for="starts_at">Desde</label>
                    <input class="trebbia-input" id="starts_at" name="starts_at" type="time" value="{{ $attributes['starts_at'] }}" required>
                </div>

                <div>
                    <label class="trebbia-label" for="ends_at">Hasta</label>
                    <input class="trebbia-input" id="ends_at" name="ends_at" type="time" value="{{ $attributes['ends_at'] }}" required>
                </div>

                <div class="md:col-span-2">
                    <label class="trebbia-label" for="reason">Motivo</label>
                    <select class="trebbia-input" id="reason" name="reason" required>
                        @foreach ($reasonOptions as $reason)
                            <option value="{{ $reason }}" @selected(($attributes['reason'] ?? '') === $reason)>{{ $reason }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row md:col-span-2">
                    <button class="trebbia-button">Revisar impacto</button>
                    <a class="trebbia-button trebbia-button-secondary" href="{{ route('agenda.index', ['date' => $returnDate]) }}">Volver a agenda</a>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Siguiente paso</h2>
                <p class="mt-2 text-sm leading-6 text-[#64716d]">Primero revisa el impacto. Si todo esta correcto, confirma el bloqueo. Puedes bloquear una fecha puntual o repetir el mismo horario en varios dias.</p>
            </section>
        </aside>
    </div>

    @if ($preview)
        <section class="trebbia-card mt-6 overflow-hidden">
            <div class="border-b border-[#e7ebe7] p-5">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Impacto detectado</p>
                <h2 class="mt-1 text-xl font-bold">
                    {{ $impact->count() }} cita{{ $impact->count() === 1 ? '' : 's' }} afectada{{ $impact->count() === 1 ? '' : 's' }}
                </h2>
            </div>

            <div class="divide-y divide-[#e7ebe7]">
                @forelse ($impact as $appointment)
                    <div class="grid gap-3 p-5 md:grid-cols-[8rem_1fr_10rem] md:items-center">
                        <div>
                            <p class="font-bold">{{ $appointment->starts_at->format('d/m/Y') }}</p>
                            <p class="text-sm text-[#64716d]">{{ $appointment->starts_at->format('H:i') }} - {{ $appointment->ends_at->format('H:i') }}</p>
                        </div>
                        <div>
                            <p class="font-bold">{{ $appointment->service?->name ?: 'Servicio sin asignar' }}</p>
                            <p class="text-sm text-[#64716d]">{{ $appointment->client?->name ?: 'Cliente sin asignar' }} / {{ $appointment->professional?->name ?: 'Profesional sin asignar' }}</p>
                            @if ($appointment->resource)
                                <p class="text-sm text-[#64716d]">Recurso: {{ $appointment->resource->name }}</p>
                            @endif
                        </div>
                        <span class="w-fit rounded-md bg-[#fff7ed] px-2 py-1 text-xs font-bold text-[#8a3027]">Necesita revision</span>
                    </div>
                @empty
                    <p class="p-5 text-sm text-[#64716d]">No hay citas afectadas por este bloqueo.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('blocked-times.store') }}" class="border-t border-[#e7ebe7] p-5">
                @csrf
                @foreach (['scope', 'professional_id', 'resource_id', 'mode', 'date', 'date_from', 'date_to', 'starts_at', 'ends_at', 'reason'] as $field)
                    <input type="hidden" name="{{ $field }}" value="{{ $attributes[$field] ?? '' }}">
                @endforeach
                @foreach ($attributes['weekdays'] ?? [] as $weekday)
                    <input type="hidden" name="weekdays[]" value="{{ $weekday }}">
                @endforeach
                <label class="mb-4 flex items-start gap-3 text-sm font-semibold text-[#53615d]">
                    <input class="mt-1" type="checkbox" name="confirm_impact" value="1" required>
                    <span>Entiendo el impacto mostrado. Guardar el bloqueo no modifica estas citas automaticamente.</span>
                </label>
                <button class="trebbia-button">Guardar bloqueo</button>
            </form>
        </section>
    @endif

    <script>
        const scope = document.getElementById('scope');
        const mode = document.getElementById('mode');
        const professionalField = document.getElementById('professional-field');
        const resourceField = document.getElementById('resource-field');
        const singleFields = document.querySelectorAll('[data-mode-field="single"]');
        const multipleFields = document.querySelectorAll('[data-mode-field="multiple"]');
        const date = document.getElementById('date');
        const dateFrom = document.getElementById('date_from');
        const dateTo = document.getElementById('date_to');

        function syncScopeFields() {
            professionalField.hidden = scope.value !== 'professional';
            resourceField.hidden = scope.value !== 'resource';
        }

        function syncModeFields() {
            const isMultiple = mode.value === 'multiple';

            singleFields.forEach((field) => field.hidden = isMultiple);
            multipleFields.forEach((field) => field.hidden = ! isMultiple);
            date.required = ! isMultiple;
            dateFrom.required = isMultiple;
            dateTo.required = isMultiple;
        }

        scope.addEventListener('change', syncScopeFields);
        mode.addEventListener('change', syncModeFields);
        syncScopeFields();
        syncModeFields();
    </script>
@endsection
