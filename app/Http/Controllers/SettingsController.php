<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\WhatsAppAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $business = app('activeBusiness');
        $settings = $business->settings()->firstOrCreate([]);

        return view('settings.index', [
            'business' => $business,
            'settings' => $settings,
            'whatsappAccount' => $business->whatsappAccounts()->latest()->first(),
            'branches' => $business->branches()->latest()->get(),
            'users' => $business->businessUsers()->with('user')->latest()->get(),
            'invitations' => $business->invitations()->where('status', 'pending')->latest()->get(),
            'professionals' => $business->professionals()->orderBy('name')->get(),
            'roles' => config('trebbia.roles'),
            'userLimit' => $business->subscription?->plan?->limits['users'] ?? null,
            'timezones' => $this->timezones(),
            'currencies' => $this->currencies(),
            'slotIntervals' => [10, 15, 20, 30, 45, 60],
            'bookingNoticeOptions' => [
                0 => 'Sin aviso minimo',
                60 => '1 hora',
                120 => '2 horas',
                240 => '4 horas',
                1440 => '1 dia',
                2880 => '2 dias',
            ],
            'whatsappDefaults' => $this->defaultWhatsappSettings($business),
            'appointmentStatusOptions' => [
                'scheduled' => 'Programada',
                'confirmed' => 'Confirmada',
            ],
        ]);
    }

    public function updateBusiness(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'industry' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['required_if:enabled,1', 'nullable', 'string', 'max:60'],
            'timezone' => ['required', Rule::in(array_keys($this->timezones()))],
            'currency' => ['required', Rule::in(array_keys($this->currencies()))],
        ]);

        app('activeBusiness')->update($attributes);

        return redirect()->route('settings.index')->with('status', 'Perfil del negocio actualizado.');
    }

    public function updatePreferences(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'slot_interval_minutes' => ['required', 'integer', Rule::in([10, 15, 20, 30, 45, 60])],
            'booking_notice_minutes' => ['required', 'integer', Rule::in([0, 60, 120, 240, 1440, 2880])],
            'allow_public_booking' => ['nullable', 'boolean'],
            'require_manual_confirmation' => ['nullable', 'boolean'],
            'notify_email' => ['nullable', 'boolean'],
            'notify_whatsapp' => ['nullable', 'boolean'],
        ]);

        app('activeBusiness')->settings()->firstOrCreate([])->update([
            'slot_interval_minutes' => $attributes['slot_interval_minutes'],
            'booking_notice_minutes' => $attributes['booking_notice_minutes'],
            'public_booking_settings' => [
                'allow_public_booking' => $request->boolean('allow_public_booking'),
                'require_manual_confirmation' => $request->boolean('require_manual_confirmation'),
            ],
            'notification_preferences' => [
                'email' => $request->boolean('notify_email'),
                'whatsapp' => $request->boolean('notify_whatsapp'),
            ],
        ]);

        return redirect()->route('settings.index')->with('status', 'Preferencias actualizadas.');
    }

    public function updateWhatsapp(Request $request): RedirectResponse
    {
        $business = app('activeBusiness');
        $attributes = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:60'],
            'display_name' => ['nullable', 'string', 'max:140'],
            'entry_message' => ['required', 'string', 'max:240'],
            'welcome_message' => ['required', 'string', 'max:500'],
            'unavailable_message' => ['required', 'string', 'max:500'],
            'confirmation_message' => ['required', 'string', 'max:500'],
            'appointment_status' => ['required', Rule::in(['scheduled', 'confirmed'])],
            'phone_number_id' => ['nullable', 'string', 'max:120'],
            'waba_id' => ['nullable', 'string', 'max:120'],
            'access_token' => ['nullable', 'string', 'max:4000'],
            'billing_owner_confirmed' => ['nullable', 'boolean'],
        ]);

        abort_if(
            ($attributes['phone_number_id'] ?? null) && ! $request->boolean('billing_owner_confirmed'),
            422,
            'Confirma que la WABA, el numero y la facturacion pertenecen al negocio.',
        );

        $cloudApiConfigured = filled($attributes['phone_number_id'] ?? null);
        $settings = array_merge($this->defaultWhatsappSettings($business), [
            'enabled' => $request->boolean('enabled'),
            'phone' => $this->normalizeWhatsappPhone($attributes['phone'] ?? ''),
            'display_name' => $attributes['display_name'] ?: $business->name,
            'entry_message' => $attributes['entry_message'],
            'welcome_message' => $attributes['welcome_message'],
            'unavailable_message' => $attributes['unavailable_message'],
            'confirmation_message' => $attributes['confirmation_message'],
            'appointment_status' => $attributes['appointment_status'],
            'mode' => $cloudApiConfigured ? 'cloud_api_manual' : 'simulated',
            'status' => $request->boolean('enabled') ? ($cloudApiConfigured ? 'configured' : 'simulated') : 'disabled',
            'billing_model' => $cloudApiConfigured ? 'business_owned' : null,
            'billing_owner_confirmed' => $cloudApiConfigured && $request->boolean('billing_owner_confirmed'),
        ]);

        $business->settings()->firstOrCreate([])->update(['whatsapp_settings' => $settings]);

        if ($attributes['phone_number_id'] ?? null) {
            abort_if(
                WhatsAppAccount::where('phone_number_id', $attributes['phone_number_id'])
                    ->where('business_id', '!=', $business->id)
                    ->exists(),
                422,
                'Este phone number ID ya esta asociado a otro negocio.',
            );

            $account = WhatsAppAccount::updateOrCreate(
                ['phone_number_id' => $attributes['phone_number_id']],
                [
                    'business_id' => $business->id,
                    'display_name' => $settings['display_name'],
                    'phone' => $settings['phone'],
                    'waba_id' => $attributes['waba_id'] ?? null,
                    'is_active' => $settings['enabled'],
                    'status' => $settings['enabled'] ? 'configured' : 'disabled',
                    'metadata' => [
                        'mode' => 'cloud_api_manual',
                        'billing_model' => 'business_owned',
                        'billing_owner_confirmed' => true,
                    ],
                ],
            );

            if ($attributes['access_token'] ?? null) {
                $account->update(['access_token' => $attributes['access_token']]);
            }
        }

        return redirect()->route('settings.index')->with('status', 'Canal WhatsApp actualizado.');
    }

    public function storeBranch(Request $request): RedirectResponse
    {
        $branch = app('activeBusiness')->branches()->create($this->validatedBranch($request));
        $this->syncMainBranch($branch, $request->boolean('is_main'));

        return redirect()->route('settings.index')->with('status', 'Sede creada.');
    }

    public function updateBranch(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranch($branch);
        $branch->update($this->validatedBranch($request));
        $this->syncMainBranch($branch, $request->boolean('is_main'));

        return redirect()->route('settings.index')->with('status', 'Sede actualizada.');
    }

    private function validatedBranch(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'phone' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:240'],
            'is_main' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]) + [
            'is_main' => $request->boolean('is_main'),
            'is_active' => $request->boolean('is_active'),
        ];
    }

    private function syncMainBranch(Branch $branch, bool $isMain): void
    {
        if (! $isMain) {
            return;
        }

        app('activeBusiness')->branches()
            ->whereKeyNot($branch->id)
            ->update(['is_main' => false]);
    }

    private function authorizeBranch(Branch $branch): void
    {
        abort_unless($branch->business_id === app('activeBusiness')->id, 404);
    }

    private function timezones(): array
    {
        return [
            'America/Bogota' => 'Bogota',
            'America/Lima' => 'Lima',
            'America/Mexico_City' => 'Ciudad de Mexico',
            'America/New_York' => 'Nueva York',
            'America/Santiago' => 'Santiago',
            'Europe/Madrid' => 'Madrid',
        ];
    }

    private function currencies(): array
    {
        return [
            'COP' => 'COP',
            'USD' => 'USD',
            'MXN' => 'MXN',
            'PEN' => 'PEN',
            'CLP' => 'CLP',
            'EUR' => 'EUR',
        ];
    }

    private function defaultWhatsappSettings($business): array
    {
        return [
            'enabled' => false,
            'phone' => $this->normalizeWhatsappPhone($business->phone ?? ''),
            'display_name' => $business->name,
            'entry_message' => 'Hola, quiero agendar una cita',
            'welcome_message' => 'Hola, soy el asistente de reservas de '.$business->name.'. Te ayudo a encontrar un horario disponible.',
            'unavailable_message' => 'No encontre horarios disponibles para esa opcion. Probemos con otra fecha u horario.',
            'confirmation_message' => 'Listo, tu cita quedo registrada. Te esperamos.',
            'appointment_status' => 'scheduled',
            'mode' => 'simulated',
            'status' => 'not_configured',
            'billing_model' => null,
            'billing_owner_confirmed' => false,
        ];
    }

    private function normalizeWhatsappPhone(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?: '';
    }
}
