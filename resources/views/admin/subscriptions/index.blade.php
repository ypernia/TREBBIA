@extends('layouts.admin')

@section('title', 'Suscripciones | TREBBIA ADMIN')
@section('page-title', 'Suscripciones')

@section('content')
    <section class="trebbia-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[72rem] text-left text-sm">
                <thead class="bg-[#f8fafc] text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">
                    <tr>
                        <th class="px-5 py-3">Business</th>
                        <th class="px-5 py-3">Estado actual</th>
                        <th class="px-5 py-3">Trial</th>
                        <th class="px-5 py-3">Renovacion</th>
                        <th class="px-5 py-3">Actualizar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ebe7]">
                    @foreach ($subscriptions as $subscription)
                        <tr class="align-top">
                            <td class="px-5 py-4">
                                @if ($subscription->business)
                                    <a href="{{ route('admin.businesses.show', $subscription->business) }}" class="font-bold text-[#0f5f59]">{{ $subscription->business->name }}</a>
                                @else
                                    <p class="font-bold">Sin business</p>
                                @endif
                                <p class="text-[#64748b]">{{ $subscription->business?->owner?->email }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-bold">{{ $statusLabels[$subscription->status] ?? $subscription->status }}</p>
                                <p class="text-[#64748b]">{{ $subscription->plan?->name ?? 'Trial sin plan' }}</p>
                            </td>
                            <td class="px-5 py-4">
                                {{ $subscription->trial_ends_at?->format('d/m/Y') ?? 'No aplica' }}
                            </td>
                            <td class="px-5 py-4">
                                {{ $subscription->current_period_ends_at?->format('d/m/Y') ?? 'Pendiente' }}
                            </td>
                            <td class="px-5 py-4">
                                <form method="POST" action="{{ route('admin.subscriptions.update', $subscription) }}" class="grid gap-3 lg:grid-cols-[10rem_13rem_1fr_auto]">
                                    @csrf
                                    @method('PATCH')
                                    <select class="trebbia-input" name="status" required>
                                        @foreach ($statusLabels as $value => $label)
                                            <option value="{{ $value }}" @selected($subscription->status === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <select class="trebbia-input" name="plan_id">
                                        <option value="">Sin cambiar plan</option>
                                        @foreach ($plans as $plan)
                                            <option value="{{ $plan->id }}" @selected($subscription->plan_id === $plan->id)>{{ $plan->name }}</option>
                                        @endforeach
                                    </select>
                                    <input class="trebbia-input" name="reason" placeholder="Motivo de auditoria" required>
                                    <button class="trebbia-button">Guardar</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-5">{{ $subscriptions->links() }}</div>
@endsection
