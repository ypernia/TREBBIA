<?php

namespace App\Http\Controllers;

use App\Models\WhatsAppActivationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WhatsAppActivationController extends Controller
{
    public function create(): View
    {
        $business = app('activeBusiness');

        return view('whatsapp-activation.create', [
            'business' => $business,
            'activationRequest' => $business->whatsappActivationRequests()->latest()->first(),
            'statusLabels' => $this->statusLabels(),
            'categories' => $this->categories(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $business = app('activeBusiness');
        $attributes = $request->validate([
            'commercial_name' => ['required', 'string', 'max:160'],
            'legal_name' => ['nullable', 'string', 'max:180'],
            'tax_id' => ['nullable', 'string', 'max:80'],
            'country' => ['required', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:180'],
            'industry' => ['nullable', 'string', 'max:120'],
            'website_or_instagram' => ['nullable', 'string', 'max:180'],
            'public_email' => ['nullable', 'email', 'max:180'],
            'public_phone' => ['nullable', 'string', 'max:60'],
            'responsible_name' => ['required', 'string', 'max:160'],
            'responsible_role' => ['nullable', 'string', 'max:120'],
            'responsible_email' => ['required', 'email', 'max:180'],
            'responsible_phone' => ['required', 'string', 'max:60'],
            'whatsapp_number' => ['required', 'string', 'max:60'],
            'verification_method' => ['required', Rule::in(['sms', 'call', 'both'])],
            'uses_whatsapp_business' => ['nullable', 'boolean'],
            'whatsapp_display_name' => ['required', 'string', 'max:160'],
            'whatsapp_description' => ['nullable', 'string', 'max:500'],
            'whatsapp_category' => ['nullable', 'string', 'max:120'],
            'business_hours' => ['nullable', 'string', 'max:500'],
            'number_owner_confirmed' => ['accepted'],
            'managed_activation_accepted' => ['accepted'],
            'messaging_costs_accepted' => ['accepted'],
            'client_notes' => ['nullable', 'string', 'max:800'],
        ]);

        $attributes['uses_whatsapp_business'] = $request->boolean('uses_whatsapp_business');
        $attributes['number_owner_confirmed'] = true;
        $attributes['managed_activation_accepted'] = true;
        $attributes['messaging_costs_accepted'] = true;
        $attributes['status'] = WhatsAppActivationRequest::STATUS_SUBMITTED;

        $business->whatsappActivationRequests()->create($attributes);

        return redirect()
            ->route('whatsapp-activation.create')
            ->with('status', 'Solicitud de WhatsApp automatico enviada. TREBBIA revisara los datos para iniciar la activacion.');
    }

    private function statusLabels(): array
    {
        return [
            WhatsAppActivationRequest::STATUS_SUBMITTED => 'Solicitud enviada',
            WhatsAppActivationRequest::STATUS_REVIEWING => 'En revision',
            WhatsAppActivationRequest::STATUS_NUMBER_VALIDATION => 'Pendiente de validacion del numero',
            WhatsAppActivationRequest::STATUS_CONFIGURING => 'En configuracion',
            WhatsAppActivationRequest::STATUS_ACTIVE => 'Activo',
            WhatsAppActivationRequest::STATUS_NEEDS_INFO => 'Requiere ajuste',
        ];
    }

    private function categories(): array
    {
        return [
            'Salud y bienestar',
            'Belleza',
            'Barberia',
            'Servicios profesionales',
            'Educacion',
            'Restaurante',
            'Retail',
            'Otro',
        ];
    }
}
