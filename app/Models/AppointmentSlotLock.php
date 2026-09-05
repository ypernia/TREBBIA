<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentSlotLock extends Model
{
    protected $fillable = [
        'business_id',
        'lock_key',
        'lock_date',
        'scope',
        'scope_id',
    ];

    protected function casts(): array
    {
        return [
            'lock_date' => 'date',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
