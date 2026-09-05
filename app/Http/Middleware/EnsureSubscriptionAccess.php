<?php

namespace App\Http\Middleware;

use App\Models\Subscription;
use App\Services\SubscriptionManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSubscriptionAccess
{
    private array $allowedWhenLocked = [
        'membership.index',
        'membership.update',
        'settings.index',
        'settings.business.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $business = app('activeBusiness');
        $subscription = app(SubscriptionManager::class)->ensure($business);

        app()->instance('activeSubscription', $subscription);
        view()->share('activeSubscription', $subscription);

        if ($subscription->hasOperationalAccess() || $request->routeIs(...$this->allowedWhenLocked)) {
            return $next($request);
        }

        $message = $subscription->status === Subscription::STATUS_EXPIRED
            ? 'Tu periodo de prueba finalizo. Selecciona una membresia para continuar utilizando TREBBIA.'
            : 'Tu membresia no esta activa. Selecciona o regulariza una membresia para continuar.';

        return redirect()->route('membership.index')->with('status', $message);
    }
}
