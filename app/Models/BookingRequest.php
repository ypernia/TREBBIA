<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRequest extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_ACCEPTED = 'accepted';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_EXPIRED = 'expired';

    protected $fillable = [
        'business_id',
        'branch_id',
        'client_id',
        'professional_id',
        'service_id',
        'resource_id',
        'appointment_id',
        'starts_at',
        'ends_at',
        'status',
        'source_channel',
        'source_reference',
        'idempotency_key',
        'source_metadata',
        'notes',
        'requested_at',
        'decided_at',
        'decided_by',
        'decision_notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'requested_at' => 'datetime',
            'decided_at' => 'datetime',
            'source_metadata' => 'array',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_ACCEPTED => 'Aceptada',
            self::STATUS_REJECTED => 'Rechazada',
            self::STATUS_EXPIRED => 'Expirada',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function decidedBy()
    {
        return $this->belongsTo(User::class, 'decided_by');
    }
}
