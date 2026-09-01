<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $business = app('activeBusiness');
        $search = $request->string('q')->toString();

        return view('clients.index', [
            'business' => $business,
            'search' => $search,
            'clients' => $business->clients()
                ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                }))
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('clients.form', ['client' => new Client, 'business' => app('activeBusiness')]);
    }

    public function store(Request $request)
    {
        app('activeBusiness')->clients()->create($this->validated($request));

        return redirect()->route('clientes.index')->with('status', 'Cliente creado.');
    }

    public function edit(Client $cliente)
    {
        $this->authorizeTenant($cliente);

        return view('clients.form', ['client' => $cliente, 'business' => app('activeBusiness')]);
    }

    public function update(Request $request, Client $cliente)
    {
        $this->authorizeTenant($cliente);
        $cliente->update($this->validated($request));

        return redirect()->route('clientes.index')->with('status', 'Cliente actualizado.');
    }

    public function destroy(Client $cliente)
    {
        $this->authorizeTenant($cliente);
        $cliente->delete();

        return redirect()->route('clientes.index')->with('status', 'Cliente archivado.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:60'],
            'birthdate' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1200'],
        ]);
    }

    private function authorizeTenant(Client $client): void
    {
        abort_unless($client->business_id === app('activeBusiness')->id, 404);
    }
}
