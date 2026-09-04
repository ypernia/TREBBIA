<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\ClinicalRecord;
use App\Support\ModuleAvailability;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClinicalRecordController extends Controller
{
    public function index()
    {
        $business = app('activeBusiness');
        abort_unless(ModuleAvailability::clinicalHistory($business), 404);
        $search = request()->string('q')->toString();

        return view('clinical-records.index', [
            'business' => $business,
            'search' => $search,
            'clients' => $business->clients()
                ->when($search, fn ($query) => $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('document_number', 'like', "%{$search}%");
                }))
                ->withCount(['appointments', 'clinicalRecords'])
                ->where('is_active', true)
                ->orderBy('name')
                ->take(8)
                ->get(),
            'records' => $business->clinicalRecords()
                ->with(['client', 'professional', 'appointment.service'])
                ->latest('record_date')
                ->latest()
                ->paginate(12),
            'recordsCount' => $business->clinicalRecords()->count(),
            'clientsCount' => $business->clients()->count(),
        ]);
    }

    public function store(Request $request, Client $cliente)
    {
        $this->authorizeClient($cliente);
        abort_unless(ModuleAvailability::clinicalHistory(app('activeBusiness')), 404);

        $cliente->clinicalRecords()->create($this->payload($request, $cliente));

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('status', 'Historia clinica guardada.');
    }

    public function update(Request $request, Client $cliente, ClinicalRecord $clinicalRecord)
    {
        $this->authorizeClient($cliente);
        abort_unless(ModuleAvailability::clinicalHistory(app('activeBusiness')), 404);
        $this->authorizeRecord($clinicalRecord, $cliente);

        $clinicalRecord->update($this->payload($request, $cliente));

        return redirect()
            ->route('clientes.show', $cliente)
            ->with('status', 'Historia clinica actualizada.');
    }

    private function payload(Request $request, Client $client): array
    {
        $business = app('activeBusiness');

        $data = $request->validate([
            'record_date' => ['required', 'date'],
            'appointment_id' => ['nullable', 'integer', Rule::exists('appointments', 'id')->where('business_id', $business->id)->where('client_id', $client->id)],
            'professional_id' => ['nullable', 'integer', Rule::exists('professionals', 'id')->where('business_id', $business->id)],
            'reason_for_visit' => ['nullable', 'string', 'max:180'],
            'diagnosis' => ['nullable', 'string', 'max:2500'],
            'pain_scale' => ['nullable', 'integer', 'min:0', 'max:10'],
            'subjective' => ['nullable', 'string', 'max:2500'],
            'objective' => ['nullable', 'string', 'max:2500'],
            'assessment' => ['nullable', 'string', 'max:2500'],
            'treatment_plan' => ['nullable', 'string', 'max:2500'],
            'evolution' => ['nullable', 'string', 'max:2500'],
            'recommendations' => ['nullable', 'string', 'max:2500'],
            'next_steps' => ['nullable', 'string', 'max:2500'],
            'status' => ['required', Rule::in(array_keys(ClinicalRecord::statusLabels()))],
        ]);

        return [
            ...$data,
            'business_id' => $business->id,
        ];
    }

    private function authorizeClient(Client $client): void
    {
        abort_unless($client->business_id === app('activeBusiness')->id, 404);
    }

    private function authorizeRecord(ClinicalRecord $record, Client $client): void
    {
        abort_unless($record->business_id === app('activeBusiness')->id && $record->client_id === $client->id, 404);
    }
}
