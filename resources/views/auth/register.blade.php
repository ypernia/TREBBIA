@extends('layouts.auth')

@section('title', 'Registro | TREBBIA')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-5 py-10">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="block">
                <x-trebbia-logo class="w-56" />
            </a>
            <div class="trebbia-card mt-6 p-6">
                <h1 class="text-2xl font-bold">Crear cuenta</h1>
                <p class="mt-2 text-sm text-[#64716d]">Empieza con tu usuario. Luego crearemos tu negocio y activaremos una prueba gratuita de 14 dias.</p>
                <div class="mt-5">@include('partials.errors')</div>
                <form method="POST" action="{{ route('register.store') }}" class="mt-5 space-y-4">
                    @csrf
                    <div>
                        <label class="trebbia-label" for="name">Nombre</label>
                        <input class="trebbia-input" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    </div>
                    <div>
                        <label class="trebbia-label" for="email">Correo</label>
                        <input class="trebbia-input" id="email" type="email" name="email" value="{{ old('email') }}" required>
                    </div>
                    <div>
                        <label class="trebbia-label" for="password">Contrasena</label>
                        <input class="trebbia-input" id="password" type="password" name="password" required>
                    </div>
                    <div>
                        <label class="trebbia-label" for="password_confirmation">Confirmar contrasena</label>
                        <input class="trebbia-input" id="password_confirmation" type="password" name="password_confirmation" required>
                    </div>
                    <button class="trebbia-button w-full">Crear cuenta</button>
                </form>
                <p class="mt-5 text-center text-sm text-[#64716d]">Ya tienes cuenta? <a class="font-bold text-[#245f57]" href="{{ route('login') }}">Inicia sesion</a></p>
            </div>
        </div>
    </div>
@endsection
