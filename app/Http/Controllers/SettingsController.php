<?php

namespace App\Http\Controllers;

use App\Models\Branch;
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
            'branches' => $business->branches()->latest()->get(),
            'users' => $business->businessUsers()->with('user')->latest()->get(),
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
        ]);
    }

    public function updateBusiness(Request $request): RedirectResponse
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'industry' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:60'],
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
}
