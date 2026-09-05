<?php

namespace App\Http\Controllers;

use App\Models\BusinessInvitation;
use App\Models\BusinessUser;
use App\Models\Professional;
use App\Models\User;
use App\Services\PlanEntitlements;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function invite(Request $request): RedirectResponse
    {
        $business = app('activeBusiness');
        $attributes = $request->validate([
            'name' => ['nullable', 'string', 'max:140'],
            'email' => ['required', 'email', 'max:180'],
            'role' => ['required', Rule::in(array_keys(config('trebbia.roles')))],
            'professional_id' => ['nullable', Rule::exists('professionals', 'id')->where('business_id', $business->id)],
        ]);

        if (! $this->hasAvailableUserSeat()) {
            return back()->withErrors(['email' => 'El plan actual no tiene cupos de usuario disponibles.']);
        }

        $user = User::where('email', $attributes['email'])->first();
        $permissions = BusinessUser::ROLE_PERMISSIONS[$attributes['role']] ?? [];

        if ($user) {
            $member = BusinessUser::updateOrCreate(
                ['business_id' => $business->id, 'user_id' => $user->id],
                [
                    'role' => $attributes['role'],
                    'permissions' => $permissions,
                    'is_active' => true,
                    'joined_at' => now(),
                ],
            );

            $this->syncProfessionalUser($attributes['professional_id'] ?? null, $member->user_id);

            return redirect()->route('settings.index')->with('status', 'Usuario agregado al negocio.');
        }

        $business->invitations()->updateOrCreate(
            ['email' => $attributes['email']],
            [
                'invited_by' => $request->user()->id,
                'professional_id' => $attributes['professional_id'] ?? null,
                'name' => $attributes['name'] ?? null,
                'role' => $attributes['role'],
                'permissions' => $permissions,
                'token' => Str::random(48),
                'status' => BusinessInvitation::STATUS_PENDING,
            ],
        );

        return redirect()->route('settings.index')->with('status', 'Invitacion registrada.');
    }

    public function updateMember(Request $request, BusinessUser $member): RedirectResponse
    {
        $this->authorizeMember($member);

        $business = app('activeBusiness');
        $attributes = $request->validate([
            'role' => ['required', Rule::in(array_keys(config('trebbia.roles')))],
            'is_active' => ['nullable', 'boolean'],
            'professional_id' => ['nullable', Rule::exists('professionals', 'id')->where('business_id', $business->id)],
        ]);

        $isOwner = $member->role === BusinessUser::ROLE_OWNER;
        $isActive = $isOwner ? true : $request->boolean('is_active');

        if (! $member->is_active && $isActive && ! $this->hasAvailableUserSeat()) {
            return back()->withErrors(['is_active' => 'El plan actual no tiene cupos de usuario disponibles.']);
        }

        $member->update([
            'role' => $isOwner ? BusinessUser::ROLE_OWNER : $attributes['role'],
            'permissions' => BusinessUser::ROLE_PERMISSIONS[$isOwner ? BusinessUser::ROLE_OWNER : $attributes['role']] ?? [],
            'is_active' => $isActive,
        ]);

        $this->syncProfessionalUser($attributes['professional_id'] ?? null, $member->user_id);

        return redirect()->route('settings.index')->with('status', 'Usuario actualizado.');
    }

    public function cancelInvitation(BusinessInvitation $invitation): RedirectResponse
    {
        abort_unless($invitation->business_id === app('activeBusiness')->id, 404);

        $invitation->update(['status' => BusinessInvitation::STATUS_CANCELLED]);

        return redirect()->route('settings.index')->with('status', 'Invitacion cancelada.');
    }

    private function hasAvailableUserSeat(): bool
    {
        $business = app('activeBusiness');
        $entitlements = app(PlanEntitlements::class);

        return $entitlements->can($business, 'team.manage') && $entitlements->hasCapacity($business, 'users');
    }

    private function syncProfessionalUser(?int $professionalId, int $userId): void
    {
        Professional::where('business_id', app('activeBusiness')->id)
            ->where('user_id', $userId)
            ->whereKeyNot($professionalId ?: 0)
            ->update(['user_id' => null]);

        if ($professionalId) {
            Professional::where('business_id', app('activeBusiness')->id)
                ->whereKey($professionalId)
                ->update(['user_id' => $userId]);
        }
    }

    private function authorizeMember(BusinessUser $member): void
    {
        abort_unless($member->business_id === app('activeBusiness')->id, 404);
    }
}
