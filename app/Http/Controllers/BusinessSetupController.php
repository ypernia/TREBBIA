<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Business;
use App\Models\BusinessSettings;
use App\Models\BusinessUser;
use App\Services\SubscriptionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BusinessSetupController extends Controller
{
    public function create()
    {
        return view('business.create');
    }

    public function store(Request $request)
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'industry' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:60'],
            'timezone' => ['required', 'string', 'max:80'],
        ]);

        $business = Business::create([
            ...$attributes,
            'owner_id' => $request->user()->id,
            'slug' => $this->uniqueSlug($attributes['name']),
            'currency' => 'COP',
            'status' => 'onboarding',
        ]);

        BusinessUser::create([
            'business_id' => $business->id,
            'user_id' => $request->user()->id,
            'role' => BusinessUser::ROLE_OWNER,
            'permissions' => BusinessUser::ROLE_PERMISSIONS[BusinessUser::ROLE_OWNER],
            'is_active' => true,
            'joined_at' => now(),
        ]);

        Branch::create([
            'business_id' => $business->id,
            'name' => 'Sede principal',
            'phone' => $business->phone,
            'is_main' => true,
        ]);

        BusinessSettings::create(['business_id' => $business->id]);
        app(SubscriptionManager::class)->ensure($business);

        $request->session()->put('business_id', $business->id);

        return redirect()->route('onboarding.show', ['step' => 'negocio']);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'negocio';
        $slug = $base;
        $count = 2;

        while (Business::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
