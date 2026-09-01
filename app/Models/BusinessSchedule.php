<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSchedule extends Model
{
    protected $fillable = ['business_id', 'branch_id', 'weekday', 'opens_at', 'closes_at', 'is_closed'];

    protected function casts(): array
    {
        return ['is_closed' => 'boolean'];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
