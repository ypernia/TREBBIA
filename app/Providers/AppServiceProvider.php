<?php

namespace App\Providers;

use App\Contracts\BookingIntentInterpreter;
use App\Services\RuleBasedBookingIntentInterpreter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BookingIntentInterpreter::class, RuleBasedBookingIntentInterpreter::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
