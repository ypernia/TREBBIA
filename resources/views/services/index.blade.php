@extends('layouts.app')

@section('title', 'Servicios | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Servicios')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-[#64716d]">Administra el catalogo que luego alimentara la pagina publica de reservas y el motor de disponibilidad.</p>
        <a class="trebbia-button" href="{{ route('servicios.create') }}">Nuevo servicio</a>
    </div>
    @include('partials.errors')

    @if (! empty($suggestedServices))
        <section class="trebbia-card mb-6 overflow-hidden">
            <div class="border-b border-[#e7ebe7] p-5">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Sugeridos para {{ $business->industry ?: 'tu negocio' }}</p>
                <h2 class="mt-1 text-lg font-bold">Crea servicios base en segundos</h2>
                <p class="mt-1 text-sm text-[#64716d]">Selecciona los servicios que aplican y ajustalos despues si necesitas cambiar precio, duracion o descripcion.</p>
            </div>
            <form method="POST" action="{{ route('servicios.suggestions.store') }}" class="p-5">
                @csrf
                <div class="grid gap-3 lg:grid-cols-2">
                    @foreach ($suggestedServices as $index => $service)
                        @php $exists = $business->services()->where('name', $service['name'])->exists(); @endphp
                        <label class="rounded-md border border-[#e1e6e0] bg-white p-4 {{ $exists ? 'opacity-60' : '' }}">
                            <span class="flex items-start gap-3">
                                <input type="hidden" name="services[{{ $index }}][name]" value="{{ $service['name'] }}">
                                <input type="hidden" name="services[{{ $index }}][duration_minutes]" value="{{ $service['duration_minutes'] }}">
                                <input type="hidden" name="services[{{ $index }}][price]" value="{{ $service['price'] }}">
                                <input type="hidden" name="services[{{ $index }}][description]" value="{{ $service['description'] }}">
                                <input class="mt-1" type="checkbox" name="services[{{ $index }}][selected]" value="1" @checked(! $exists) @disabled($exists)>
                                <span>
                                    <span class="block font-bold">{{ $service['name'] }}</span>
                                    <span class="mt-1 block text-sm text-[#64716d]">{{ $service['duration_minutes'] }} min · ${{ number_format($service['price'], 0, ',', '.') }}</span>
                                    <span class="mt-1 block text-sm text-[#64716d]">{{ $exists ? 'Ya existe en tu catalogo.' : $service['description'] }}</span>
                                </span>
                            </span>
                        </label>
                    @endforeach
                </div>
                <button class="trebbia-button mt-5">Crear seleccionados</button>
            </form>
        </section>
    @endif

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
