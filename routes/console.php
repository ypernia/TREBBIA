<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('platform:superadmin {email}', function (string $email): int {
    $user = User::where('email', $email)->first();

    if (! $user) {
        $this->error("No existe un usuario con el correo {$email}. Primero registralo en TREBBIA.");

        return self::FAILURE;
    }

    $user->update([
        'platform_role' => 'superadmin',
        'platform_permissions' => ['*'],
        'platform_access_enabled_at' => now(),
    ]);

    $this->info("{$email} ahora es Superadmin de TREBBIA.");

    return self::SUCCESS;
})->purpose('Promote an existing TREBBIA user to platform superadmin');
