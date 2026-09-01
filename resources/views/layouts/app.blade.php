<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TREBBIA')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-[#f6f7f4] font-sans text-[#18211f] antialiased">
    @php
        $nav = [
            ['label' => 'Inicio', 'href' => route('dashboard'), 'active' => request()->routeIs('dashboard')],
            ...collect(config('trebbia.modules'))->map(fn ($item, $key) => [
                'label' => $item['label'],
                'href' => isset($item['route']) ? route($item['route']) : route('modules.show', $key),
                'active' => isset($item['route']) ? request()->routeIs(str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']) : request()->is("modulos/{$key}"),
            ])->values()->all(),
        ];
    @endphp
    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
        <aside class="border-b border-[#e1e6e0] bg-white lg:min-h-screen lg:border-b-0 lg:border-r">
            <div class="flex items-center justify-between px-5 py-5 lg:block">
                <a href="{{ route('dashboard') }}" class="block">
                    <span class="text-xl font-bold">TREBBIA</span>
                    <span class="mt-1 hidden text-sm text-[#64716d] lg:block">{{ $activeBusiness->name ?? 'Workspace' }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="lg:hidden">
                    @csrf
                    <button class="text-sm font-semibold text-[#245f57]">Salir</button>
                </form>
            </div>
            <nav class="flex gap-2 overflow-x-auto px-4 pb-4 lg:block lg:space-y-1 lg:overflow-visible">
                @foreach ($nav as $item)
                    <a href="{{ $item['href'] }}" class="block whitespace-nowrap rounded-md px-3 py-2 text-sm font-semibold {{ $item['active'] ? 'bg-[#e7f1ed] text-[#245f57]' : 'text-[#53615d] hover:bg-[#f1f4f1] hover:text-[#18211f]' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="mt-auto hidden p-4 lg:block">
                <div class="rounded-md border border-[#e1e6e0] bg-[#f7faf8] p-4">
                    <p class="text-sm font-bold">Rol activo</p>
                    <p class="mt-1 text-sm text-[#64716d]">Owner con permisos extensibles.</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button class="w-full rounded-md border border-[#d7ddd7] px-3 py-2 text-sm font-bold text-[#245f57] hover:bg-[#edf2ef]">Cerrar sesion</button>
                </form>
            </div>
        </aside>
        <main class="min-w-0">
            <header class="border-b border-[#e1e6e0] bg-white px-5 py-5 sm:px-8">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[#64716d]">@yield('eyebrow', 'Panel')</p>
                        <h1 class="text-2xl font-bold">@yield('page-title', 'Inicio')</h1>
                    </div>
                    @if (($activeBusiness ?? null)?->status === 'onboarding')
                        <a href="{{ route('onboarding.show', 'negocio') }}" class="trebbia-button trebbia-button-secondary">Completar onboarding</a>
                    @endif
                </div>
            </header>
            <section class="px-5 py-6 sm:px-8">
                @if (session('status'))
                    <div class="mb-5 rounded-md border border-[#cfe4da] bg-[#edf7f4] px-4 py-3 text-sm font-semibold text-[#245f57]">{{ session('status') }}</div>
                @endif
                @yield('content')
            </section>
        </main>
    </div>
</body>
</html>
