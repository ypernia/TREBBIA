<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConversationState extends Model
{
    protected $fillable = ['business_id', 'conversation_id', 'state', 'data', 'expires_at'];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'expires_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }
}
