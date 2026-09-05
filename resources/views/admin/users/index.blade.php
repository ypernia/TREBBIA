@extends('layouts.admin')

@section('title', 'Usuarios | TREBBIA ADMIN')
@section('page-title', 'Usuarios')

@section('content')
    <form method="GET" class="mb-5 flex gap-3">
        <input class="trebbia-input max-w-md" name="q" value="{{ $search }}" placeholder="Buscar usuario o correo">
        <button class="trebbia-button">Buscar</button>
    </form>

    <section class="trebbia-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[44rem] text-left text-sm">
                <thead class="bg-[#f8fafc] text-xs font-bold uppercase tracking-[0.12em] text-[#64748b]">
                    <tr>
                        <th class="px-5 py-3">Usuario</th>
                        <th class="px-5 py-3">Nivel plataforma</th>
                        <th class="px-5 py-3">Businesses</th>
                        <th class="px-5 py-3">Creado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#e7ebe7]">
                    @foreach ($users as $user)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-bold">{{ $user->name }}</p>
                                <p class="text-[#64748b]">{{ $user->email }}</p>
                            </td>
                            <td class="px-5 py-4">{{ $user->isPlatformAdmin() ? 'Superadmin' : 'Cliente TREBBIA' }}</td>
                            <td class="px-5 py-4">{{ $user->businesses_count }}</td>
                            <td class="px-5 py-4">{{ $user->created_at->format('d/m/Y') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-5">{{ $users->links() }}</div>
@endsection
