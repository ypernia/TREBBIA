<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsAppActivationRequest extends Model
{
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_NUMBER_VALIDATION = 'number_validation';
    public const STATUS_CONFIGURING = 'configuring';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_NEEDS_INFO = 'needs_info';

    protected $table = 'whatsapp_activation_requests';

    protected $fillable = [
        'business_id',
        'status',
        'commercial_name',
        'legal_name',
        'tax_id',
        'country',
        'city',
        'address',
        'industry',
        'website_or_instagram',
        'public_email',
        'public_phone',
        'responsible_name',
        'responsible_role',
        'responsible_email',
        'responsible_phone',
        'whatsapp_number',
        'verification_method',
        'uses_whatsapp_business',
        'whatsapp_display_name',
        'whatsapp_description',
        'whatsapp_category',
        'business_hours',
        'number_owner_confirmed',
        'managed_activation_accepted',
        'messaging_costs_accepted',
        'client_notes',
        'internal_notes',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'uses_whatsapp_business' => 'boolean',
            'number_owner_confirmed' => 'boolean',
            'managed_activation_accepted' => 'boolean',
            'messaging_costs_accepted' => 'boolean',
            'reviewed_at' => 'datetime',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
