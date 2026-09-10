<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use SoftDeletes;

    public const PRICE_FIXED = 'fixed';

    public const PRICE_RANGE = 'range';

    public const PRICE_TO_DEFINE = 'to_define';

    protected $fillable = [
        'business_id',
        'name',
        'description',
        'duration_minutes',
        'price_type',
        'price_cents',
        'price_max_cents',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'price_cents' => 'integer',
            'price_max_cents' => 'integer',
        ];
    }

    public static function priceTypeLabels(): array
    {
        return [
            self::PRICE_FIXED => 'Precio fijo',
            self::PRICE_RANGE => 'Rango de precio',
            self::PRICE_TO_DEFINE => 'Precio a definir',
        ];
    }

    public function priceLabel(): string
    {
        if ($this->price_type === self::PRICE_TO_DEFINE) {
            return 'Precio a definir';
        }

        if ($this->price_type === self::PRICE_RANGE) {
            return '$'.number_format(($this->price_cents ?? 0) / 100, 0, ',', '.')
                .' - $'.number_format(($this->price_max_cents ?? $this->price_cents ?? 0) / 100, 0, ',', '.');
        }

        return '$'.number_format(($this->price_cents ?? 0) / 100, 0, ',', '.');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function professionals()
    {
        return $this->belongsToMany(Professional::class)
            ->withPivot('business_id')
            ->withTimestamps();
    }
}
