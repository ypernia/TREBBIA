<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManualPayment extends Model
{
    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_VOID = 'void';

    protected $fillable = [
        'business_id',
        'subscription_id',
        'plan_id',
        'recorded_by',
        'status',
        'currency',
        'amount_cents',
        'period_months',
        'payment_method',
        'reference',
        'paid_at',
        'notes',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
