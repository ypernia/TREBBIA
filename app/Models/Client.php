<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = ['business_id', 'name', 'email', 'phone', 'birthdate', 'notes'];

    protected function casts(): array
    {
        return ['birthdate' => 'date'];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
