<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Support\ModuleAvailability;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $business = app('activeBusiness');
        $search = $request->string('q')->toString();
        $status = $request->input('status', 'active');

        return view('clients.index', [
            'business' => $business,
            'search' => $search,
            'status' => $status,
            'clients' => $business->clients()
                ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%");
                }))
                ->when($status === 'active', fn ($query) => $query->where('is_active', true))
                ->when($status === 'inactive', fn ($query) => $query->where('is_active', false))
                ->withCount('appointments')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    public function create()
    {
        return view('clients.form', ['client' => new Client, 'business' => app('activeBusiness')]);
    }

    public function show(Client $cliente)
    {
        $this->authorizeTenant($cliente);
        $business = app('activeBusiness');
        $showClinicalHistory = ModuleAvailability::clinicalHistory($business);

        $cliente->loadCount([
            'appointments',
            'appointments as completed_appointments_count' => fn ($query) => $query->where('status', 'completed'),
            'appointments as pending_appointments_count' => fn ($query) => $query->whereIn('status', ['scheduled', 'confirmed']),
        ]);

        return view('clients.show', [
            'business' => $business,
            'client' => $cliente,
            'showClinicalHistory' => $showClinicalHistory,
            'professionals' => $showClinicalHistory ? $business->professionals()
                ->where('is_active', true)
                ->orderBy('name')
                ->get() : collect(),
            'nextAppointment' => $cliente->appointments()
                ->with(['professional', 'service'])
                ->where('business_id', $business->id)
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->first(),
            'clinicalAppointments' => $showClinicalHistory ? $cliente->appointments()
                ->with(['professional', 'service'])
                ->where('business_id', $business->id)
                ->latest('starts_at')
                ->take(20)
                ->get() : collect(),
            'clinicalRecords' => $showClinicalHistory ? $cliente->clinicalRecords()
                ->with(['professional', 'appointment.service'])
                ->where('business_id', $business->id)
                ->latest('record_date')
                ->latest()
                ->take(10)
                ->get() : collect(),
            'appointments' => $cliente->appointments()
                ->with(['professional', 'service', 'resource'])
                ->where('business_id', $business->id)
                ->latest('starts_at')
                ->paginate(8),
        ]);
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
            'document_number' => ['nullable', 'string', 'max:80'],
            'birthdate' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1200'],
            'is_active' => ['sometimes', 'boolean'],
        ]);
    }

    private function authorizeTenant(Client $client): void
    {
        abort_unless($client->business_id === app('activeBusiness')->id, 404);
    }
}
