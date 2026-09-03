<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    protected $fillable = ['business_id', 'name', 'description', 'duration_minutes', 'price_cents', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function professionals()
    {
        return $this->belongsToMany(Professional::class)
            ->withPivot('business_id')
            ->withTimestamps();
    }
}
