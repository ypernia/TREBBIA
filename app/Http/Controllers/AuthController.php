<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $attributes = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create($attributes);

        Auth::login($user);

        return redirect()->route('business.create');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Las credenciales no coinciden con nuestros registros.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        if ($request->user()->isPlatformAdmin()) {
            return redirect()->intended(route('admin.dashboard'));
        }

        $business = $request->user()->businesses()->wherePivot('is_active', true)->first();

        if (! $business) {
            return redirect()->route('business.create');
        }

        $request->session()->put('business_id', $business->id);

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
