@extends('layouts.app')

@section('title', 'Recursos | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Recursos')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-[#64716d]">Crea recursos reservables sin empezar desde cero. TREBBIA sugiere recursos segun la actividad del negocio.</p>
        <a class="trebbia-button trebbia-button-secondary" href="{{ route('recursos.create') }}">Formulario completo</a>
    </div>

    @include('partials.errors')

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <main class="space-y-6">
            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Crear recurso rapido</h2>
                <form method="POST" action="{{ route('recursos.store') }}" class="mt-4 grid gap-3 lg:grid-cols-[1fr_12rem_1fr_8rem_auto] lg:items-end">
                    @csrf
                    <div>
                        <label class="trebbia-label" for="quick_name">Nombre</label>
                        <input class="trebbia-input" id="quick_name" name="name" placeholder="Cabina 3" required>
                    </div>
                    <div>
                        <label class="trebbia-label" for="quick_type">Tipo</label>
                        <input class="trebbia-input" id="quick_type" name="type" placeholder="Cabina">
                    </div>
                    <div>
                        <label class="trebbia-label" for="quick_branch_id">Sede</label>
                        <select class="trebbia-input" id="quick_branch_id" name="branch_id">
                            <option value="">Sin sede</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-[#53615d]">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked>
                        Activo
                    </label>
                    <button class="trebbia-button">Crear</button>
                </form>
            </section>

            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <h2 class="text-lg font-bold">Recursos actuales</h2>
                </div>
                @forelse ($resources as $resource)
                    <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[1fr_12rem_10rem] md:items-center">
                        <div>
                            <p class="font-bold">{{ $resource->name }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">{{ $resource->type ?: 'Sin tipo' }} - {{ $resource->branch?->name ?: 'Sin sede' }}</p>
                        </div>
                        <span class="w-fit rounded-md px-2 py-1 text-xs font-bold {{ $resource->is_active ? 'bg-[#edf7f4] text-[#245f57]' : 'bg-[#f1f1ef] text-[#64716d]' }}">{{ $resource->is_active ? 'Activo' : 'Inactivo' }}</span>
                        <div class="flex items-center gap-2 md:justify-end">
                            <a class="rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('recursos.edit', $resource) }}">Editar</a>
                            <form method="POST" action="{{ route('recursos.destroy', $resource) }}">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-md border border-[#f0c9c4] px-3 py-2 text-sm font-bold text-[#8a3027]">Archivar</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center">
                        <p class="font-bold">No hay recursos todavia</p>
                        <p class="mt-2 text-sm text-[#64716d]">Usa los sugeridos o crea uno personalizado para empezar mas rapido.</p>
                    </div>
                @endforelse
            </section>
            <div>{{ $resources->links() }}</div>
        </main>

        <aside class="trebbia-card p-5">
            <h2 class="text-lg font-bold">Sugeridos para {{ $business->industry ?: 'tu negocio' }}</h2>
            <form method="POST" action="{{ route('recursos.suggestions.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="trebbia-label" for="suggestion_branch_id">Sede</label>
                    <select class="trebbia-input" id="suggestion_branch_id" name="branch_id">
                        <option value="">Sin sede</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-2">
                    @foreach ($suggestedResources as $index => $resource)
                        <div class="rounded-md border border-[#e1e6e0] bg-white p-3">
                            <label class="flex items-start gap-3">
                                <input type="hidden" name="resources[{{ $index }}][name]" value="{{ $resource['name'] }}">
                                <input type="hidden" name="resources[{{ $index }}][type]" value="{{ $resource['type'] }}">
                                <input class="mt-1" type="checkbox" name="resources[{{ $index }}][selected]" value="1" checked>
                                <span>
                                    <span class="block font-bold">{{ $resource['name'] }}</span>
                                    <span class="mt-1 block text-sm text-[#64716d]">{{ $resource['type'] }}</span>
                                </span>
                            </label>
                        </div>
                    @endforeach
                </div>

                <button class="trebbia-button w-full">Crear seleccionados</button>
            </form>
        </aside>
    </div>
@endsection
