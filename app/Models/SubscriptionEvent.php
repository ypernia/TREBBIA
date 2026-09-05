<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionEvent extends Model
{
    protected $fillable = [
        'business_id',
        'subscription_id',
        'from_status',
        'to_status',
        'from_plan_id',
        'to_plan_id',
        'reason',
        'metadata',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function fromPlan()
    {
        return $this->belongsTo(Plan::class, 'from_plan_id');
    }

    public function toPlan()
    {
        return $this->belongsTo(Plan::class, 'to_plan_id');
    }
}
