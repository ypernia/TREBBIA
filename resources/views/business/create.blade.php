@extends('layouts.auth')

@section('title', 'Crear empresa | TREBBIA')

@section('content')
    <div class="flex min-h-screen items-center justify-center px-5 py-10">
        <div class="w-full max-w-2xl">
            <p class="text-sm font-bold text-[#245f57]">Paso inicial</p>
            <h1 class="mt-2 text-3xl font-bold">Crea tu empresa</h1>
            <p class="mt-2 text-[#64716d]">Este negocio sera el espacio principal de clientes, servicios, agenda y equipo. Iniciara con una prueba gratuita de 14 dias.</p>
            <div class="trebbia-card mt-6 p-6">
                @include('partials.errors')
                <form method="POST" action="{{ route('business.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                    @csrf
                    <div class="sm:col-span-2">
                        <label class="trebbia-label" for="name">Nombre del negocio</label>
                        <input class="trebbia-input" id="name" name="name" value="{{ old('name') }}" required autofocus>
                    </div>
                    <div>
                        <label class="trebbia-label" for="industry">Tipo de negocio</label>
                        <input class="trebbia-input" id="industry" name="industry" value="{{ old('industry') }}" placeholder="Fisioterapia, belleza, salud...">
                    </div>
                    <div>
                        <label class="trebbia-label" for="timezone">Zona horaria</label>
                        <select class="trebbia-input" id="timezone" name="timezone">
                            <option value="America/Bogota">America/Bogota</option>
                            <option value="America/Mexico_City">America/Mexico_City</option>
                            <option value="America/Lima">America/Lima</option>
                            <option value="America/New_York">America/New_York</option>
                        </select>
                    </div>
                    <div>
                        <label class="trebbia-label" for="email">Correo del negocio</label>
                        <input class="trebbia-input" id="email" type="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div>
                        <label class="trebbia-label" for="phone">Telefono</label>
                        <input class="trebbia-input" id="phone" name="phone" value="{{ old('phone') }}">
                    </div>
                    <div class="sm:col-span-2">
                        <button class="trebbia-button">Crear empresa y continuar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
