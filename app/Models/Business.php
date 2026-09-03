<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'slug',
        'industry',
        'email',
        'phone',
        'timezone',
        'currency',
        'status',
        'onboarding_completed_at',
    ];

    protected function casts(): array
    {
        return [
            'onboarding_completed_at' => 'datetime',
        ];
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'business_users')
            ->withPivot(['role', 'permissions', 'is_active', 'joined_at'])
            ->withTimestamps();
    }

    public function businessUsers()
    {
        return $this->hasMany(BusinessUser::class);
    }

    public function branches()
    {
        return $this->hasMany(Branch::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function professionals()
    {
        return $this->hasMany(Professional::class);
    }

    public function clients()
    {
        return $this->hasMany(Client::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function appointmentReminders()
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    public function notificationTemplates()
    {
        return $this->hasMany(NotificationTemplate::class);
    }

    public function settings()
    {
        return $this->hasOne(BusinessSettings::class);
    }

    public function schedules()
    {
        return $this->hasMany(BusinessSchedule::class);
    }
}
