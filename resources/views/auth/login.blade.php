@extends('layouts.auth')

@section('title', 'Login | TREBBIA')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-5 py-10">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="block">
                <x-trebbia-logo class="w-56" />
            </a>
            <div class="trebbia-card mt-6 p-6">
                <h1 class="text-2xl font-bold">Iniciar sesion</h1>
                <p class="mt-2 text-sm text-[#64716d]">Accede a tu workspace de reservas.</p>
                @if (session('status'))
                    <div class="mt-5 rounded-md border border-[#cfe4da] bg-[#edf7f4] px-4 py-3 text-sm text-[#245f57]">{{ session('status') }}</div>
                @endif
                <div class="mt-5">@include('partials.errors')</div>
                <form method="POST" action="{{ route('login.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="trebbia-label" for="email">Correo</label>
                        <input class="trebbia-input" id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
                    </div>
                    <div>
                        <label class="trebbia-label" for="password">Contrasena</label>
                        <input class="trebbia-input" id="password" type="password" name="password" required>
                    </div>
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <label class="flex items-center gap-2 text-[#53615d]">
                            <input type="checkbox" name="remember" class="rounded border-[#d7ddd7]"> Recordarme
                        </label>
                        <a href="{{ route('password.request') }}" class="font-bold text-[#245f57]">Recuperar</a>
                    </div>
                    <button class="trebbia-button w-full">Entrar</button>
                </form>
                <p class="mt-5 text-center text-sm text-[#64716d]">No tienes cuenta? <a class="font-bold text-[#245f57]" href="{{ route('register') }}">Registrate</a></p>
            </div>
        </div>
    </div>
@endsection
