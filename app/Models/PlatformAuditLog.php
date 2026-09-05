<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class PlatformAuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'business_id',
        'reason',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    protected function casts(): array
    {
        return ['metadata' => 'array'];
    }

    public static function record(Request $request, string $action, ?Model $auditable = null, array $metadata = [], ?int $businessId = null, ?string $reason = null): self
    {
        return self::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => $auditable ? $auditable::class : null,
            'auditable_id' => $auditable?->getKey(),
            'business_id' => $businessId,
            'reason' => $reason,
            'metadata' => $metadata,
            'ip_address' => $request->ip(),
            'user_agent' => str($request->userAgent() ?? '')->limit(500)->toString(),
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
