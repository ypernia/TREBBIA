@extends('layouts.app')

@section('title', 'Recursos | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Recursos')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-[#64716d]">Registra consultorios, cabinas, equipos u otros elementos que podran limitar disponibilidad.</p>
        <a class="trebbia-button" href="{{ route('recursos.create') }}">Nuevo recurso</a>
    </div>
    <div class="trebbia-card overflow-hidden">
        @forelse ($resources as $resource)
            <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[1fr_12rem_10rem] md:items-center">
                <div>
                    <p class="font-bold">{{ $resource->name }}</p>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $resource->type ?: 'Sin tipo' }} · {{ $resource->branch?->name ?: 'Sin sede' }}</p>
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
                <p class="mt-2 text-sm text-[#64716d]">Agrega recursos fisicos si tu disponibilidad depende de salas, cabinas o equipos.</p>
                <a class="trebbia-button mt-5" href="{{ route('recursos.create') }}">Crear recurso</a>
            </div>
        @endforelse
    </div>
    <div class="mt-5">{{ $resources->links() }}</div>
@endsection
