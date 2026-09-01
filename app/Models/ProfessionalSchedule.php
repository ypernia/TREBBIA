<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfessionalSchedule extends Model
{
    protected $fillable = ['business_id', 'professional_id', 'weekday', 'starts_at', 'ends_at', 'is_closed'];

    protected function casts(): array
    {
        return ['is_closed' => 'boolean'];
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }
}
