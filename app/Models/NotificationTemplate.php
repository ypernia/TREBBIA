<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'business_id',
        'name',
        'channel',
        'trigger',
        'subject',
        'body',
        'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function reminders()
    {
        return $this->hasMany(AppointmentReminder::class);
    }
}
