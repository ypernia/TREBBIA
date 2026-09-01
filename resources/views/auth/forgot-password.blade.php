@extends('layouts.auth')

@section('title', 'Recuperar contrasena | TREBBIA')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-5 py-10">
        <div class="trebbia-card w-full max-w-md p-6">
            <h1 class="text-2xl font-bold">Recuperar contrasena</h1>
            <p class="mt-2 text-sm text-[#64716d]">Te enviaremos un enlace si el correo existe.</p>
            @if (session('status'))
                <div class="mt-5 rounded-md border border-[#cfe4da] bg-[#edf7f4] px-4 py-3 text-sm text-[#245f57]">{{ session('status') }}</div>
            @endif
            <div class="mt-5">@include('partials.errors')</div>
            <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">
                @csrf
                <div>
                    <label class="trebbia-label" for="email">Correo</label>
                    <input class="trebbia-input" id="email" type="email" name="email" required>
                </div>
                <button class="trebbia-button w-full">Enviar enlace</button>
            </form>
        </div>
    </div>
@endsection
