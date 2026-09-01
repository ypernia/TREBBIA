<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBusinessSelected
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $businessId = $request->session()->get('business_id');
        $business = $businessId ? $user->businesses()
            ->where('businesses.id', $businessId)
            ->wherePivot('is_active', true)
            ->first() : null;

        if (! $business) {
            $business = $user->businesses()->wherePivot('is_active', true)->first();
        }

        if (! $business) {
            return redirect()->route('business.create');
        }

        $request->session()->put('business_id', $business->id);
        app()->instance('activeBusiness', $business);
        view()->share('activeBusiness', $business);

        return $next($request);
    }
}
