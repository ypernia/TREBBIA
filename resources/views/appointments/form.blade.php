@extends('layouts.app')

@section('title', ($appointment->exists ? 'Editar cita' : 'Nueva cita').' | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', $appointment->exists ? 'Editar cita' : 'Nueva cita')

@section('content')
    @php
        $selectedDate = old('date', request('date', $appointment->exists ? $appointment->starts_at->format('Y-m-d') : today()->format('Y-m-d')));
        $selectedTime = old('starts_at', request('starts_at', $appointment->exists ? $appointment->starts_at->format('H:i') : '09:00'));
        $selectedClient = old('client_id', request('client_id', $appointment->client_id));
        $selectedService = old('service_id', request('service_id', $appointment->service_id));
        $selectedProfessional = old('professional_id', request('professional_id', $appointment->professional_id));
        $selectedResource = old('resource_id', request('resource_id', $appointment->resource_id));
        $selectedBranch = old('branch_id', request('branch_id', $appointment->branch_id));
        $selectedStatus = old('status', request('status', $appointment->status ?: 'scheduled'));
    @endphp

    <div class="trebbia-card max-w-4xl p-6">
        @include('partials.errors')

        @if ($clients->isEmpty() || $professionals->isEmpty() || $services->isEmpty())
            <div class="rounded-md border border-[#f0dfb8] bg-[#fff9eb] px-4 py-3 text-sm text-[#765214]">
                Antes de agendar necesitas al menos un cliente, un profesional activo y un servicio activo.
            </div>
        @endif

        @if ($availabilityWarnings !== [])
            <div class="mt-4 rounded-md border border-[#f0dfb8] bg-[#fff9eb] px-4 py-3 text-sm text-[#765214]">
                <p class="font-bold">Disponibilidad por revisar</p>
                <ul class="mt-2 list-disc space-y-1 pl-5">
                    @foreach ($availabilityWarnings as $warning)
                        <li>{{ $warning }}</li>
                    @endforeach
                </ul>
            </div>
        @elseif ($selectedService && $selectedProfessional && $selectedDate && $selectedTime)
            <div class="mt-4 rounded-md border border-[#cfe4da] bg-[#edf7f4] px-4 py-3 text-sm font-semibold text-[#245f57]">
                El horario seleccionado no muestra conflictos conocidos.
            </div>
        @endif

        <form method="POST" action="{{ $appointment->exists ? route('agenda.update', $appointment) : route('agenda.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
            @csrf
            @if ($appointment->exists)
                @method('PUT')
            @endif

            <div>
                <label class="trebbia-label" for="date">Fecha</label>
                <input class="trebbia-input" id="date" type="date" name="date" value="{{ $selectedDate }}" required>
            </div>
            <div>
                <label class="trebbia-label" for="starts_at">Hora inicial</label>
                <input class="trebbia-input" id="starts_at" type="time" name="starts_at" value="{{ $selectedTime }}" required>
            </div>
            <div>
                <label class="trebbia-label" for="client_id">Cliente</label>
                <select class="trebbia-input" id="client_id" name="client_id">
                    <option value="">Sin cliente</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}" @selected((string) $selectedClient === (string) $client->id)>{{ $client->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="trebbia-label" for="service_id">Servicio</label>
                <select class="trebbia-input" id="service_id" name="service_id" required>
                    <option value="">Seleccionar</option>
                    @foreach ($services as $service)
                        <option value="{{ $service->id }}" @selected((string) $selectedService === (string) $service->id)>{{ $service->name }} - {{ $service->duration_minutes }} min</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="trebbia-label" for="professional_id">Profesional</label>
                <select class="trebbia-input" id="professional_id" name="professional_id" required>
                    <option value="">Seleccionar</option>
                    @foreach ($professionals as $professional)
                        <option value="{{ $professional->id }}" @selected((string) $selectedProfessional === (string) $professional->id)>{{ $professional->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="trebbia-label" for="resource_id">Recurso</label>
                <select class="trebbia-input" id="resource_id" name="resource_id">
                    <option value="">Sin recurso</option>
                    @foreach ($resources as $resource)
                        <option value="{{ $resource->id }}" @selected((string) $selectedResource === (string) $resource->id)>{{ $resource->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="trebbia-label" for="branch_id">Sede</label>
                <select class="trebbia-input" id="branch_id" name="branch_id">
                    <option value="">Sin sede</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) $selectedBranch === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="trebbia-label" for="status">Estado</label>
                <select class="trebbia-input" id="status" name="status">
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected($selectedStatus === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2">
                <label class="trebbia-label" for="notes">Notas</label>
                <textarea class="trebbia-input" id="notes" name="notes" rows="4">{{ old('notes', $appointment->notes) }}</textarea>
            </div>
            <div class="flex flex-col gap-3 sm:col-span-2 sm:flex-row">
                <button class="trebbia-button" @disabled($professionals->isEmpty() || $services->isEmpty())>Guardar cita</button>
                <a class="trebbia-button trebbia-button-secondary" href="{{ route('agenda.index', ['date' => $selectedDate]) }}">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
