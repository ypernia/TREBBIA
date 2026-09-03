<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = ['business_id', 'name', 'email', 'phone', 'document_number', 'birthdate', 'notes', 'is_active'];

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
