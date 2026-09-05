@extends('layouts.admin')

@section('title', 'WhatsApp | TREBBIA ADMIN')
@section('page-title', 'WhatsApp')

@section('content')
    <section class="trebbia-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[58rem] text-left text-sm">
                <thead class="bg-[#f8fafc] text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">
                    <tr>
                        <th class="px-5 py-3">Business</th>
                        <th class="px-5 py-3">Numero</th>
                        <th class="px-5 py-3">Responsable</th>
                        <th class="px-5 py-3">Estado</th>
                        <th class="px-5 py-3">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ebe7]">
                    @forelse ($requests as $request)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-bold">{{ $request->business?->name }}</p>
                                <p class="text-[#64748b]">{{ $request->business?->owner?->email }}</p>
                            </td>
                            <td class="px-5 py-4">{{ $request->whatsapp_number }}</td>
                            <td class="px-5 py-4">
                                <p>{{ $request->responsible_name }}</p>
                                <p class="text-[#64748b]">{{ $request->responsible_email }}</p>
                            </td>
                            <td class="px-5 py-4">{{ $request->status }}</td>
                            <td class="px-5 py-4">{{ $request->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-8 text-center text-[#64748b]">Sin solicitudes WhatsApp.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-5">{{ $requests->links() }}</div>
@endsection
