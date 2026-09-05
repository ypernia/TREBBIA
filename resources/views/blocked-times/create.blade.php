@extends('layouts.app')

@section('title', 'Bloquear tiempo | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Bloquear tiempo')

@section('content')
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

                <div>
                    <label class="trebbia-label" for="date">Fecha</label>
                    <input class="trebbia-input" id="date" name="date" type="date" value="{{ $attributes['date'] }}" required>
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
                    <a class="trebbia-button trebbia-button-secondary" href="{{ route('agenda.index', ['date' => $attributes['date']]) }}">Volver a agenda</a>
                </div>
            </form>
        </section>

        <aside class="space-y-6">
            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Siguiente paso</h2>
                <p class="mt-2 text-sm leading-6 text-[#64716d]">Primero revisa el impacto. Si todo esta correcto, confirma el bloqueo. En una fase posterior TREBBIA ayudara a resolver citas afectadas.</p>
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
                @foreach (['scope', 'professional_id', 'resource_id', 'date', 'starts_at', 'ends_at', 'reason'] as $field)
                    <input type="hidden" name="{{ $field }}" value="{{ $attributes[$field] ?? '' }}">
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
        const professionalField = document.getElementById('professional-field');
        const resourceField = document.getElementById('resource-field');

        function syncScopeFields() {
            professionalField.hidden = scope.value !== 'professional';
            resourceField.hidden = scope.value !== 'resource';
        }

        scope.addEventListener('change', syncScopeFields);
        syncScopeFields();
    </script>
@endsection
