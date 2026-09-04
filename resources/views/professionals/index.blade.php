@extends('layouts.app')

@section('title', 'Profesionales | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Profesionales')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-[#64716d]">Gestiona el equipo, sus servicios, sede y disponibilidad propia para alimentar la agenda.</p>
        <a class="trebbia-button" href="{{ route('profesionales.create') }}">Nuevo profesional</a>
    </div>
    <div class="trebbia-card overflow-hidden">
        @forelse ($professionals as $professional)
            <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[1fr_12rem_10rem] md:items-center">
                <div>
                    <p class="font-bold">{{ $professional->name }}</p>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $professional->title ?: 'Sin especialidad' }} - {{ $professional->branch?->name ?: 'Sin sede' }}</p>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $professional->email ?: 'Sin correo' }}{{ $professional->phone ? ' - '.$professional->phone : '' }}</p>
                    <p class="mt-2 text-sm text-[#64716d]">
                        Servicios:
                        {{ $professional->services->isNotEmpty() ? $professional->services->pluck('name')->join(', ') : 'Sin servicios asignados' }}
                    </p>
                </div>
                <span class="w-fit rounded-md px-2 py-1 text-xs font-bold {{ $professional->is_active ? 'bg-[#edf7f4] text-[#245f57]' : 'bg-[#f1f1ef] text-[#64716d]' }}">{{ $professional->is_active ? 'Activo' : 'Inactivo' }}</span>
                <div class="flex items-center gap-2 md:justify-end">
                    <a class="trebbia-icon-button" href="{{ route('profesionales.edit', $professional) }}" title="Editar profesional" aria-label="Editar profesional {{ $professional->name }}">
                        <x-icon name="edit" class="size-4" />
                    </a>
                    <form method="POST" action="{{ route('profesionales.destroy', $professional) }}" onsubmit="return confirm('Archivar este profesional?');">
                        @csrf
                        @method('DELETE')
                        <button class="trebbia-icon-button trebbia-icon-button-danger" title="Archivar profesional" aria-label="Archivar profesional {{ $professional->name }}">
                            <x-icon name="archive" class="size-4" />
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <x-empty-state
                icon="users"
                title="No hay profesionales todavia"
                body="Agrega el primero y asigna sus servicios para que pueda recibir citas."
                :action="route('profesionales.create')"
                action-label="Crear profesional"
            />
        @endforelse
    </div>
    <div class="mt-5">{{ $professionals->links() }}</div>
@endsection
