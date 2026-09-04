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
}
