<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Professional extends Model
{
    use SoftDeletes;

    protected $fillable = ['business_id', 'branch_id', 'user_id', 'name', 'email', 'phone', 'title', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedules()
    {
        return $this->hasMany(ProfessionalSchedule::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class)
            ->withPivot('business_id')
            ->withTimestamps();
    }

    public function clinicalRecords()
    {
        return $this->hasMany(ClinicalRecord::class);
    }
}
