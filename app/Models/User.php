<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'platform_role',
        'platform_permissions',
        'platform_access_enabled_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'platform_permissions' => 'array',
            'platform_access_enabled_at' => 'datetime',
        ];
    }

    public function businessUsers()
    {
        return $this->hasMany(BusinessUser::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(Business::class, 'business_users')
            ->withPivot(['role', 'permissions', 'is_active', 'joined_at'])
            ->withTimestamps();
    }

    public function ownedBusinesses()
    {
        return $this->hasMany(Business::class, 'owner_id');
    }

    public function activeBusiness(): ?Business
    {
        $businessId = session('business_id');

        if (! $businessId) {
            return null;
        }

        return $this->businesses()
            ->where('businesses.id', $businessId)
            ->wherePivot('is_active', true)
            ->first();
    }

    public function isPlatformAdmin(): bool
    {
        return $this->platform_role === 'superadmin' && $this->platform_access_enabled_at !== null;
    }
}
