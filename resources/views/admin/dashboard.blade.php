@extends('layouts.admin')

@section('title', 'Panel | TREBBIA ADMIN')
@section('page-title', 'Panel de plataforma')

@section('content')
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ([
            'Businesses' => $metrics['businesses'],
            'Usuarios' => $metrics['users'],
            'Trials activos' => $metrics['trials'],
            'Suscripciones activas' => $metrics['activeSubscriptions'],
            'Trials expirados' => $metrics['expiredSubscriptions'],
            'Solicitudes WhatsApp' => $metrics['whatsappRequests'],
        ] as $label => $value)
            <section class="trebbia-card p-5">
                <p class="text-sm font-bold text-[#64748b]">{{ $label }}</p>
                <p class="mt-2 text-3xl font-bold">{{ $value }}</p>
            </section>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <section class="trebbia-card overflow-hidden">
            <div class="border-b border-[#e7ebe7] p-5">
                <h2 class="text-lg font-bold">Businesses recientes</h2>
            </div>
            <div class="divide-y divide-[#e7ebe7]">
                @forelse ($recentBusinesses as $business)
                    <div class="p-5">
                        <p class="font-bold">{{ $business->name }}</p>
                        <p class="mt-1 text-sm text-[#64748b]">{{ $business->owner?->email }} · {{ $business->subscription?->status ?? 'sin suscripcion' }}</p>
                    </div>
                @empty
                    <p class="p-5 text-sm text-[#64748b]">Sin negocios registrados.</p>
                @endforelse
            </div>
        </section>

        <section class="trebbia-card overflow-hidden">
            <div class="border-b border-[#e7ebe7] p-5">
                <h2 class="text-lg font-bold">Suscripciones recientes</h2>
            </div>
            <div class="divide-y divide-[#e7ebe7]">
                @forelse ($recentSubscriptions as $subscription)
                    <div class="p-5">
                        <p class="font-bold">{{ $subscription->business?->name }}</p>
                        <p class="mt-1 text-sm text-[#64748b]">{{ $subscription->status }} · {{ $subscription->plan?->name ?? 'Trial sin plan' }}</p>
                    </div>
                @empty
                    <p class="p-5 text-sm text-[#64748b]">Sin suscripciones registradas.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
