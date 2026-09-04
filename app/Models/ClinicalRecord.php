<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClinicalRecord extends Model
{
    public const STATUS_DRAFT = 'draft';

    public const STATUS_FINAL = 'final';

    protected $fillable = [
        'business_id',
        'client_id',
        'appointment_id',
        'professional_id',
        'record_date',
        'reason_for_visit',
        'diagnosis',
        'pain_scale',
        'subjective',
        'objective',
        'assessment',
        'treatment_plan',
        'evolution',
        'recommendations',
        'next_steps',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'record_date' => 'date',
            'pain_scale' => 'integer',
        ];
    }

    public static function statusLabels(): array
    {
        return [
            self::STATUS_DRAFT => 'Borrador',
            self::STATUS_FINAL => 'Finalizada',
        ];
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function professional()
    {
        return $this->belongsTo(Professional::class);
    }
}
