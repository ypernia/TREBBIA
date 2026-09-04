<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessSettings extends Model
{
    protected $fillable = [
        'business_id',
        'slot_interval_minutes',
        'booking_notice_minutes',
        'notification_preferences',
        'public_booking_settings',
        'whatsapp_settings',
    ];

    protected function casts(): array
    {
        return [
            'notification_preferences' => 'array',
            'public_booking_settings' => 'array',
            'whatsapp_settings' => 'array',
        ];
    }
}
