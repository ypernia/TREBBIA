@extends('layouts.admin')

@section('title', 'Auditoria | TREBBIA ADMIN')
@section('page-title', 'Auditoria de plataforma')

@section('content')
    <section class="trebbia-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[58rem] text-left text-sm">
                <thead class="bg-[#f8fafc] text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">
                    <tr>
                        <th class="px-5 py-3">Fecha</th>
                        <th class="px-5 py-3">Superadmin</th>
                        <th class="px-5 py-3">Accion</th>
                        <th class="px-5 py-3">Business</th>
                        <th class="px-5 py-3">Motivo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ebe7]">
                    @forelse ($logs as $log)
                        <tr>
                            <td class="px-5 py-4">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-4">{{ $log->user?->email ?? 'Sistema' }}</td>
                            <td class="px-5 py-4">{{ $log->action }}</td>
                            <td class="px-5 py-4">{{ $log->business?->name ?? 'No aplica' }}</td>
                            <td class="px-5 py-4">{{ $log->reason ?? 'Sin motivo' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-[#64748b]">Sin eventos de auditoria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-5">{{ $logs->links() }}</div>
@endsection
