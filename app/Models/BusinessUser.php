<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessUser extends Model
{
    public const ROLE_OWNER = 'owner';

    public const ROLE_ADMIN = 'admin';

    public const ROLE_RECEPTIONIST = 'receptionist';

    public const ROLE_PROFESSIONAL = 'professional';

    public const ROLE_PERMISSIONS = [
        self::ROLE_OWNER => ['*'],
        self::ROLE_ADMIN => ['manage_business', 'manage_calendar', 'manage_clients', 'manage_catalog', 'view_reports'],
        self::ROLE_RECEPTIONIST => ['manage_calendar', 'manage_clients', 'view_catalog'],
        self::ROLE_PROFESSIONAL => ['view_calendar', 'manage_own_availability', 'view_clients'],
    ];

    protected $fillable = [
        'business_id',
        'user_id',
        'role',
        'permissions',
        'is_active',
        'joined_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'is_active' => 'boolean',
            'joined_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
