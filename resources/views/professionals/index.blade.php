@extends('layouts.app')

@section('title', 'Profesionales | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Profesionales')

@section('content')
    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-[#64716d]">Gestiona el equipo que atendera servicios y tendra disponibilidad propia en la agenda.</p>
        <a class="trebbia-button" href="{{ route('profesionales.create') }}">Nuevo profesional</a>
    </div>
    <div class="trebbia-card overflow-hidden">
        @forelse ($professionals as $professional)
            <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[1fr_12rem_10rem] md:items-center">
                <div>
                    <p class="font-bold">{{ $professional->name }}</p>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $professional->title ?: 'Sin especialidad' }} · {{ $professional->branch?->name ?: 'Sin sede' }}</p>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $professional->email ?: 'Sin correo' }} {{ $professional->phone ? '· '.$professional->phone : '' }}</p>
                </div>
                <span class="w-fit rounded-md px-2 py-1 text-xs font-bold {{ $professional->is_active ? 'bg-[#edf7f4] text-[#245f57]' : 'bg-[#f1f1ef] text-[#64716d]' }}">{{ $professional->is_active ? 'Activo' : 'Inactivo' }}</span>
                <div class="flex items-center gap-2 md:justify-end">
                    <a class="rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('profesionales.edit', $professional) }}">Editar</a>
                    <form method="POST" action="{{ route('profesionales.destroy', $professional) }}">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-md border border-[#f0c9c4] px-3 py-2 text-sm font-bold text-[#8a3027]">Archivar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <p class="font-bold">No hay profesionales todavia</p>
                <p class="mt-2 text-sm text-[#64716d]">Agrega el primer profesional para preparar agenda y disponibilidad.</p>
                <a class="trebbia-button mt-5" href="{{ route('profesionales.create') }}">Crear profesional</a>
            </div>
        @endforelse
    </div>
    <div class="mt-5">{{ $professionals->links() }}</div>
@endsection
