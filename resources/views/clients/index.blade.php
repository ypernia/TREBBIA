@extends('layouts.app')

@section('title', 'Clientes | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Clientes')

@section('content')
    <div class="mb-5 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-end">
        <form method="GET" action="{{ route('clientes.index') }}" class="trebbia-card grid gap-3 p-4 md:grid-cols-[1fr_12rem_auto] md:items-end">
            <div>
                <label class="trebbia-label" for="q">Busqueda</label>
                <input class="trebbia-input" id="q" name="q" value="{{ $search }}" placeholder="Nombre, correo, telefono o documento">
            </div>
            <div>
                <label class="trebbia-label" for="status">Estado</label>
                <select class="trebbia-input" id="status" name="status">
                    <option value="active" @selected($status === 'active')>Activos</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactivos</option>
                    <option value="all" @selected($status === 'all')>Todos</option>
                </select>
            </div>
            <button class="trebbia-button trebbia-button-secondary">Buscar</button>
        </form>
        <a class="trebbia-button" href="{{ route('clientes.create') }}">Nuevo cliente</a>
    </div>

    <div class="trebbia-card overflow-hidden">
        @forelse ($clients as $client)
            <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[1fr_12rem_9rem_13rem] md:items-center">
                <div>
                    <a class="font-bold text-[#245f57] hover:underline" href="{{ route('clientes.show', $client) }}">{{ $client->name }}</a>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $client->email ?: 'Sin correo' }}{{ $client->phone ? ' - '.$client->phone : '' }}</p>
                    @if ($client->document_number)
                        <p class="mt-1 text-sm text-[#64716d]">Documento: {{ $client->document_number }}</p>
                    @endif
                    @if ($client->notes)
                        <p class="mt-1 text-sm text-[#64716d]">{{ str($client->notes)->limit(90) }}</p>
                    @endif
                </div>
                <div class="text-sm text-[#64716d]">
                    <p>{{ $client->birthdate?->format('d/m/Y') ?: 'Sin nacimiento' }}</p>
                    <p>{{ $client->appointments_count }} cita{{ $client->appointments_count === 1 ? '' : 's' }}</p>
                </div>
                <span class="w-fit rounded-md px-2 py-1 text-xs font-bold {{ $client->is_active ? 'bg-[#edf7f4] text-[#245f57]' : 'bg-[#f1f1ef] text-[#53615d]' }}">
                    {{ $client->is_active ? 'Activo' : 'Inactivo' }}
                </span>
                <div class="flex items-center gap-2 md:justify-end">
                    <a class="rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('clientes.show', $client) }}">Ver</a>
                    <a class="rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57]" href="{{ route('clientes.edit', $client) }}">Editar</a>
                    <form method="POST" action="{{ route('clientes.destroy', $client) }}">
                        @csrf
                        @method('DELETE')
                        <button class="rounded-md border border-[#f0c9c4] px-3 py-2 text-sm font-bold text-[#8a3027]">Archivar</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="p-8 text-center">
                <p class="font-bold">No hay clientes todavia</p>
                <p class="mt-2 text-sm text-[#64716d]">Crea una base de clientes para luego agendar citas y consultar historial.</p>
                <a class="trebbia-button mt-5" href="{{ route('clientes.create') }}">Crear cliente</a>
            </div>
        @endforelse
    </div>

    <div class="mt-5">{{ $clients->links() }}</div>
@endsection
