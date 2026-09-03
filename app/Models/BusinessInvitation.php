<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessInvitation extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'business_id',
        'invited_by',
        'professional_id',
        'name',
        'email',
        'role',
        'permissions',
        'token',
        'status',
        'accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'permissions' => 'array',
            'accepted_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function inviter()
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }
}
