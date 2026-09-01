@extends('layouts.app')

@section('title', 'Clientes | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Clientes')

@section('content')
    <div class="mb-5 grid gap-3 lg:grid-cols-[1fr_auto] lg:items-center">
        <form method="GET" action="{{ route('clientes.index') }}" class="flex gap-2">
            <input class="trebbia-input max-w-md" name="q" value="{{ $search }}" placeholder="Buscar por nombre, correo o telefono">
            <button class="trebbia-button trebbia-button-secondary">Buscar</button>
        </form>
        <a class="trebbia-button" href="{{ route('clientes.create') }}">Nuevo cliente</a>
    </div>
    <div class="trebbia-card overflow-hidden">
        @forelse ($clients as $client)
            <div class="grid gap-3 border-b border-[#e7ebe7] p-5 md:grid-cols-[1fr_12rem_10rem] md:items-center">
                <div>
                    <p class="font-bold">{{ $client->name }}</p>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $client->email ?: 'Sin correo' }} {{ $client->phone ? '· '.$client->phone : '' }}</p>
                    @if ($client->notes)
                        <p class="mt-1 text-sm text-[#64716d]">{{ str($client->notes)->limit(90) }}</p>
                    @endif
                </div>
                <p class="text-sm text-[#64716d]">{{ $client->birthdate?->format('d/m/Y') ?: 'Sin fecha' }}</p>
                <div class="flex items-center gap-2 md:justify-end">
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
