<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    public const STATUS_TRIALING = 'trialing';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_PAST_DUE = 'past_due';

    public const STATUS_EXPIRED = 'expired';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'business_id',
        'plan_id',
        'status',
        'trial_started_at',
        'trial_ends_at',
        'current_period_started_at',
        'current_period_ends_at',
        'cancelled_at',
        'ended_at',
    ];

    protected function casts(): array
    {
        return [
            'trial_started_at' => 'datetime',
            'trial_ends_at' => 'datetime',
            'current_period_started_at' => 'datetime',
            'current_period_ends_at' => 'datetime',
            'cancelled_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isTrialing(): bool
    {
        return $this->status === self::STATUS_TRIALING && $this->trial_ends_at?->isFuture();
    }

    public function isExpired(): bool
    {
        return in_array($this->status, [self::STATUS_EXPIRED, self::STATUS_CANCELLED, self::STATUS_SUSPENDED], true)
            || ($this->status === self::STATUS_TRIALING && $this->trial_ends_at?->isPast());
    }

    public function hasOperationalAccess(): bool
    {
        return $this->isTrialing() || $this->status === self::STATUS_ACTIVE;
    }

    public function trialDaysRemaining(): int
    {
        if (! $this->trial_ends_at || $this->status !== self::STATUS_TRIALING) {
            return 0;
        }

        return (int) max(0, now()->startOfDay()->diffInDays($this->trial_ends_at->copy()->startOfDay(), false));
    }
}
