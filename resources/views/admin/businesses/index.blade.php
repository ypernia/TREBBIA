@extends('layouts.admin')

@section('title', 'Businesses | TREBBIA ADMIN')
@section('page-title', 'Businesses')

@section('content')
    <form method="GET" class="mb-5 flex gap-3">
        <input class="trebbia-input max-w-md" name="q" value="{{ $search }}" placeholder="Buscar negocio, slug, correo u owner">
        <button class="trebbia-button">Buscar</button>
    </form>

    <section class="trebbia-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[56rem] text-left text-sm">
                <thead class="bg-[#f8fafc] text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">
                    <tr>
                        <th class="px-5 py-3">Business</th>
                        <th class="px-5 py-3">Owner</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3">Membresia</th>
                        <th class="px-5 py-3">Uso</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ebe7]">
                    @forelse ($businesses as $business)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-bold">{{ $business->name }}</p>
                                <p class="text-[#64748b]">{{ $business->slug }}</p>
                            </td>
                            <td class="px-5 py-4">{{ $business->owner?->email }}</td>
                            <td class="px-5 py-4">{{ $business->status }}</td>
                            <td class="px-5 py-4">
                                <p>{{ $business->subscription?->status ?? 'sin suscripcion' }}</p>
                                <p class="text-[#64748b]">{{ $business->subscription?->plan?->name ?? 'Trial sin plan' }}</p>
                            </td>
                            <td class="px-5 py-4 text-[#64748b]">
                                {{ $business->clients_count }} clientes · {{ $business->appointments_count }} citas · {{ $business->professionals_count }} profesionales
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-[#64748b]">No hay negocios para mostrar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-5">{{ $businesses->links() }}</div>
@endsection
