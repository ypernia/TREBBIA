<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use SoftDeletes;

    public const SOURCE_INTERNAL = 'internal';

    public const SOURCE_PUBLIC_BOOKING = 'public_booking';

    public const SOURCE_WHATSAPP = 'whatsapp';

    public const STATUS_SCHEDULED = 'scheduled';

    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    public const CONTACT_PENDING = 'pending';

    public const CONTACT_SENT = 'sent';

    public const CONTACT_CONFIRMED = 'confirmed';

    public const CONTACT_NO_RESPONSE = 'no_response';

    public const CONTACT_CALL_REQUIRED = 'call_required';

    protected $fillable = [
        'business_id',
        'branch_id',
        'client_id',
        'professional_id',
        'service_id',
        'resource_id',
        'starts_at',
        'ends_at',
        'status',
        'source_channel',
        'source_reference',
        'idempotency_key',
        'source_metadata',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'source_metadata' => 'array',
        ];
    }

    public static function sourceLabels(): array
    {
        return [
            self::SOURCE_INTERNAL => 'Agenda interna',
            self::SOURCE_PUBLIC_BOOKING => 'Reserva publica',
            self::SOURCE_WHATSAPP => 'WhatsApp',
        ];
    }

    public function sourceLabel(): string
    {
        return self::sourceLabels()[$this->source_channel] ?? ucfirst((string) $this->source_channel);
    }

    public static function contactStatusLabels(): array
    {
        return [
            self::CONTACT_PENDING => 'Pendiente por contactar',
            self::CONTACT_SENT => 'Mensaje enviado',
            self::CONTACT_CONFIRMED => 'Cliente confirmo',
            self::CONTACT_NO_RESPONSE => 'Cliente no respondio',
            self::CONTACT_CALL_REQUIRED => 'Requiere llamada',
        ];
    }

    public function contactStatus(): ?string
    {
        return $this->source_metadata['reschedule_contact_status']
            ?? ($this->source_metadata['reschedule_contact_pending'] ?? false ? self::CONTACT_PENDING : null);
    }

    public function contactStatusLabel(): ?string
    {
        $status = $this->contactStatus();

        return $status ? self::contactStatusLabels()[$status] ?? ucfirst($status) : null;
    }

    public function needsContactFollowUp(): bool
    {
        return in_array($this->contactStatus(), [
            self::CONTACT_PENDING,
            self::CONTACT_SENT,
            self::CONTACT_NO_RESPONSE,
            self::CONTACT_CALL_REQUIRED,
        ], true);
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

    public function reminders()
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    public function clinicalRecords()
    {
        return $this->hasMany(ClinicalRecord::class);
    }
}
