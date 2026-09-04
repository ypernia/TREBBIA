@extends('layouts.app')

@section('title', 'Servicios | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Servicios')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-[#64716d]">Administra el catalogo que luego alimentara la pagina publica de reservas y el motor de disponibilidad.</p>
        <a class="trebbia-button" href="{{ route('servicios.create') }}">Nuevo servicio</a>
    </div>
    <div class="trebbia-card overflow-hidden">
        @forelse ($services as $service)
            <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[1fr_9rem_8rem_10rem_10rem] md:items-center">
                <div>
                    <p class="font-bold">{{ $service->name }}</p>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $service->description ?: 'Sin descripcion' }}</p>
                </div>
                <p class="text-sm font-semibold">{{ $service->duration_minutes }} min</p>
                <p class="text-sm font-semibold">${{ number_format($service->price_cents / 100, 0, ',', '.') }}</p>
                <p class="text-sm text-[#64716d]">{{ $service->professionals_count }} profesional{{ $service->professionals_count === 1 ? '' : 'es' }}</p>
                <div class="flex items-center gap-2 md:justify-end">
                    <span class="rounded-md px-2 py-1 text-xs font-bold {{ $service->is_active ? 'bg-[#edf7f4] text-[#245f57]' : 'bg-[#f1f1ef] text-[#64716d]' }}">{{ $service->is_active ? 'Activo' : 'Inactivo' }}</span>
                    <a class="trebbia-icon-button" href="{{ route('servicios.edit', $service) }}" title="Editar servicio" aria-label="Editar servicio {{ $service->name }}">
                        <x-icon name="edit" class="size-4" />
                    </a>
                    <form method="POST" action="{{ route('servicios.destroy', $service) }}" onsubmit="return confirm('Archivar este servicio?');">
                        @csrf
                        @method('DELETE')
                        <button class="trebbia-icon-button trebbia-icon-button-danger" title="Archivar servicio" aria-label="Archivar servicio {{ $service->name }}">
                            <x-icon name="archive" class="size-4" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <x-empty-state
                icon="briefcase"
                title="No hay servicios todavia"
                body="Agrega tu primer servicio con duracion y precio para habilitar reservas."
                :action="route('servicios.create')"
                action-label="Crear servicio"
            />
        @endforelse
    </div>
    <div class="mt-5">{{ $services->links() }}</div>
@endsection
