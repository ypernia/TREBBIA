@extends('layouts.admin')

@section('title', 'Planes | TREBBIA ADMIN')
@section('page-title', 'Planes y capacidades')

@section('content')
    <div class="grid gap-5 xl:grid-cols-3">
        @foreach ($plans as $plan)
            <section class="trebbia-card p-5">
                <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#245f57]">{{ $plan->code }}</p>
                <h2 class="mt-2 text-xl font-bold">{{ $plan->name }}</h2>
                <p class="mt-2 text-sm leading-6 text-[#64748b]">{{ $plan->description }}</p>
                <p class="mt-5 text-2xl font-bold">${{ number_format($plan->monthly_price_cents / 100, 0, ',', '.') }}/mes</p>
                <div class="mt-5 space-y-2 text-sm text-[#53615d]">
                    @foreach ($plan->features ?? [] as $feature)
                        <p>{{ $feature }}</p>
                    @endforeach
                </div>
                <div class="mt-5 border-t border-[#e7ebe7] pt-4 text-xs text-[#64748b]">
                    <p class="font-bold text-[#18211f]">Limites</p>
                    @foreach ($plan->limits ?? [] as $key => $value)
                        <p class="mt-1">{{ $key }}: {{ $value === null ? 'ilimitado' : $value }}</p>
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
@endsection
