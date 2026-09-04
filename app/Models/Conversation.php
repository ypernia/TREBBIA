<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    public const CHANNEL_WHATSAPP = 'whatsapp';

    public const STATUS_OPEN = 'open';

    public const STATUS_CLOSED = 'closed';

    protected $fillable = [
        'business_id',
        'whatsapp_contact_id',
        'appointment_id',
        'channel',
        'status',
        'intent',
        'current_step',
        'last_message_at',
    ];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function whatsappContact()
    {
        return $this->belongsTo(WhatsAppContact::class, 'whatsapp_contact_id');
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function messages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function state()
    {
        return $this->hasOne(ConversationState::class);
    }
}
