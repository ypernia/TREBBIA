<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'TREBBIA ADMIN')</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=nunito-sans:400,500,600,700" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="min-h-screen bg-[#0f172a] font-sans text-[#e2e8f0] antialiased">
    @php
        $nav = [
            ['label' => 'Panel', 'href' => route('admin.dashboard'), 'active' => request()->routeIs('admin.dashboard')],
            ['label' => 'Businesses', 'href' => route('admin.businesses.index'), 'active' => request()->routeIs('admin.businesses.*')],
            ['label' => 'Usuarios', 'href' => route('admin.users.index'), 'active' => request()->routeIs('admin.users.*')],
            ['label' => 'Suscripciones', 'href' => route('admin.subscriptions.index'), 'active' => request()->routeIs('admin.subscriptions.*')],
            ['label' => 'Pagos', 'href' => route('admin.payments.index'), 'active' => request()->routeIs('admin.payments.*')],
            ['label' => 'Planes', 'href' => route('admin.plans.index'), 'active' => request()->routeIs('admin.plans.*')],
            ['label' => 'WhatsApp', 'href' => route('admin.whatsapp.index'), 'active' => request()->routeIs('admin.whatsapp.*')],
            ['label' => 'Auditoria', 'href' => route('admin.audit.index'), 'active' => request()->routeIs('admin.audit.*')],
        ];
    @endphp

    <div class="min-h-screen lg:grid lg:grid-cols-[17rem_1fr]">
        <aside class="border-b border-white/10 bg-[#111827] lg:min-h-screen lg:border-b-0 lg:border-r">
            <div class="px-5 py-5">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <x-trebbia-logo variant="mark" class="h-12 w-12" />
                    <div>
                        <p class="text-lg font-bold text-white">TREBBIA</p>
                        <p class="text-xs font-bold uppercase tracking-[0.22em] text-cyan-300">ADMIN</p>
                    </div>
                </a>
            </div>
            <nav class="flex gap-2 overflow-x-auto px-4 pb-4 lg:block lg:space-y-1 lg:overflow-visible">
                @foreach ($nav as $item)
                    <a href="{{ $item['href'] }}" class="flex min-h-10 items-center whitespace-nowrap rounded-md px-3 py-2 text-sm font-semibold {{ $item['active'] ? 'bg-cyan-400 text-[#0f172a]' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="hidden p-4 lg:block">
                <div class="rounded-md border border-white/10 bg-white/5 p-4">
                    <p class="text-sm font-bold text-white">{{ auth()->user()->name }}</p>
                    <p class="mt-1 break-all text-xs text-slate-400">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button class="w-full rounded-md border border-white/10 px-3 py-2 text-sm font-bold text-cyan-200 hover:bg-white/10">Cerrar sesion</button>
                </form>
            </div>
        </aside>

        <main class="min-w-0 bg-[#f8fafc] text-[#18211f]">
            <header class="border-b border-slate-200 bg-white px-5 py-5 sm:px-8">
                <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#64748b]">TREBBIA ADMIN</p>
                <h1 class="mt-1 text-2xl font-bold">@yield('page-title', 'Panel')</h1>
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
