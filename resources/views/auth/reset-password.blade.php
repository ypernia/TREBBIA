@extends('layouts.auth')

@section('title', 'Nueva contrasena | TREBBIA')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-5 py-10">
        <div class="w-full max-w-md">
            <a href="{{ route('home') }}" class="mb-6 block">
                <x-trebbia-logo class="w-56" />
            </a>
        <div class="trebbia-card p-6">
            <h1 class="text-2xl font-bold">Nueva contrasena</h1>
            <div class="mt-5">@include('partials.errors')</div>
            <form method="POST" action="{{ route('password.store') }}" class="mt-5 space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label class="trebbia-label" for="email">Correo</label>
                    <input class="trebbia-input" id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required>
                </div>
                <div>
                    <label class="trebbia-label" for="password">Contrasena</label>
                    <input class="trebbia-input" id="password" type="password" name="password" required>
                </div>
                <div>
                    <label class="trebbia-label" for="password_confirmation">Confirmar contrasena</label>
                    <input class="trebbia-input" id="password_confirmation" type="password" name="password_confirmation" required>
                </div>
                <button class="trebbia-button w-full">Guardar</button>
            </form>
        </div>
        </div>
    </div>
@endsection
