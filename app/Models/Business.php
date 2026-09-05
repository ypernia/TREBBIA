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

    public function invitations()
    {
        return $this->hasMany(BusinessInvitation::class);
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

    public function clinicalRecords()
    {
        return $this->hasMany(ClinicalRecord::class);
    }

    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function whatsappContacts()
    {
        return $this->hasMany(WhatsAppContact::class);
    }

    public function whatsappAccounts()
    {
        return $this->hasMany(WhatsAppAccount::class);
    }

    public function whatsappActivationRequests()
    {
        return $this->hasMany(WhatsAppActivationRequest::class);
    }

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
    }

    public function conversationMessages()
    {
        return $this->hasMany(ConversationMessage::class);
    }

    public function appointmentReminders()
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    public function blockedTimes()
    {
        return $this->hasMany(BlockedTime::class);
    }

    public function notificationTemplates()
    {
        return $this->hasMany(NotificationTemplate::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function subscriptionEvents()
    {
        return $this->hasMany(SubscriptionEvent::class);
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
