<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlockedTime extends Model
{
    protected $fillable = ['business_id', 'branch_id', 'professional_id', 'resource_id', 'starts_at', 'ends_at', 'reason'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }
}
