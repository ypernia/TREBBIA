@extends('layouts.onboarding')

@section('onboarding-content')
    <h1 class="text-2xl font-bold">Configuracion lista</h1>
    <p class="mt-2 text-[#64716d]">La base de tu empresa ya quedo preparada para operar y crecer con agenda, clientes y servicios.</p>
    <div class="mt-6 grid gap-4 sm:grid-cols-3">
        <div class="rounded-md border border-[#e1e6e0] p-4">
            <p class="text-2xl font-bold">{{ $business->services()->count() }}</p>
            <p class="text-sm text-[#64716d]">Servicios</p>
        </div>
        <div class="rounded-md border border-[#e1e6e0] p-4">
            <p class="text-2xl font-bold">{{ $business->professionals()->count() }}</p>
            <p class="text-sm text-[#64716d]">Profesionales</p>
        </div>
        <div class="rounded-md border border-[#e1e6e0] p-4">
            <p class="text-2xl font-bold">{{ $business->branches()->count() }}</p>
            <p class="text-sm text-[#64716d]">Sedes</p>
        </div>
    </div>
    <form method="POST" action="{{ route('onboarding.store', 'finalizar') }}" class="mt-6">
        @csrf
        <button class="trebbia-button">Entrar al dashboard</button>
    </form>
@endsection
