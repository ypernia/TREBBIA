@extends('layouts.app')

@section('title', $module['label'].' | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', $module['label'])

@section('content')
    <div class="trebbia-card p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <span class="rounded-md bg-[#edf7f4] px-3 py-1 text-sm font-bold text-[#245f57]">{{ $module['status'] }}</span>
                <h2 class="mt-5 text-2xl font-bold">{{ $module['label'] }}</h2>
                <p class="mt-2 max-w-2xl text-[#64716d]">{{ $module['summary'] }}</p>
            </div>
            <a href="{{ route('dashboard') }}" class="trebbia-button trebbia-button-secondary">Volver</a>
        </div>

        <div class="mt-8 grid gap-4 md:grid-cols-3">
            <div class="rounded-md border border-[#e1e6e0] p-4">
                <p class="font-bold">Aislamiento</p>
                <p class="mt-2 text-sm text-[#64716d]">Este modulo operara sobre el negocio activo: {{ $business->name }}.</p>
            </div>
            <div class="rounded-md border border-[#e1e6e0] p-4">
                <p class="font-bold">Permisos</p>
                <p class="mt-2 text-sm text-[#64716d]">Roles base preparados para crecer con politicas por accion.</p>
            </div>
            <div class="rounded-md border border-[#e1e6e0] p-4">
                <p class="font-bold">Siguiente entrega</p>
                <p class="mt-2 text-sm text-[#64716d]">Convertiremos esta pantalla en CRUD funcional segun prioridad.</p>
            </div>
        </div>
    </div>
@endsection
