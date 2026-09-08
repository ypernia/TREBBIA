<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessIndustries
{
    public const OTHER = '__other';

    public static function options(): array
    {
        return [
            'Fisioterapia' => 'Fisioterapia',
            'Salud' => 'Salud',
            'Spa' => 'Spa',
            'Belleza y estetica' => 'Belleza y estetica',
            'Barberia' => 'Barberia',
            'Veterinaria' => 'Veterinaria',
            'Odontologia' => 'Odontologia',
            'Psicologia' => 'Psicologia',
            'Fitness y deporte' => 'Fitness y deporte',
            'Restaurante' => 'Restaurante',
            'Servicios profesionales' => 'Servicios profesionales',
            'Educacion y clases' => 'Educacion y clases',
            'Automotriz' => 'Automotriz',
        ];
    }

    public static function validationRules(): array
    {
        return [
            'industry' => ['nullable', 'string', Rule::in([...array_keys(self::options()), self::OTHER])],
            'industry_other' => ['nullable', 'required_if:industry,'.self::OTHER, 'string', 'max:120'],
        ];
    }

    public static function resolveFromRequest(Request $request): ?string
    {
        $industry = trim((string) $request->input('industry'));

        if ($industry === self::OTHER) {
            $industry = trim((string) $request->input('industry_other'));
        }

        return $industry !== '' ? mb_convert_case($industry, MB_CASE_TITLE, 'UTF-8') : null;
    }

    public static function selectedValue(?string $industry): string
    {
        if (! filled($industry)) {
            return '';
        }

        return array_key_exists($industry, self::options()) ? $industry : self::OTHER;
    }
}
