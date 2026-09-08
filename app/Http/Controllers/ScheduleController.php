<?php

namespace App\Http\Controllers;

use App\Models\BusinessSchedule;
use Illuminate\Http\Request;

class ScheduleController extends Controller
{
    public function edit()
    {
        $business = app('activeBusiness');
        $schedules = $business->schedules()->get()->keyBy('weekday');

        return view('schedules.edit', [
            'business' => $business,
            'schedules' => $schedules,
            'weekdays' => $this->weekdays(),
        ]);
    }

    public function update(Request $request)
    {
        $attributes = $request->validate([
            'schedule' => ['required', 'array'],
            'schedule.*.opens_at' => ['nullable', 'date_format:H:i,H:i:s'],
            'schedule.*.closes_at' => ['nullable', 'date_format:H:i,H:i:s'],
            'schedule.*.is_closed' => ['nullable', 'boolean'],
        ], $this->scheduleMessages(), $this->scheduleAttributes());

        $business = app('activeBusiness');

        foreach ($this->weekdays() as $weekday => $label) {
            $row = $attributes['schedule'][$weekday] ?? [];
            $isClosed = (bool) ($row['is_closed'] ?? false);

            $request->validate([
                "schedule.{$weekday}.closes_at" => [$isClosed ? 'nullable' : 'required', 'date_format:H:i,H:i:s', "after:schedule.{$weekday}.opens_at"],
                "schedule.{$weekday}.opens_at" => [$isClosed ? 'nullable' : 'required', 'date_format:H:i,H:i:s'],
            ], $this->scheduleMessages(), $this->scheduleAttributes());

            BusinessSchedule::updateOrCreate(
                ['business_id' => $business->id, 'branch_id' => null, 'weekday' => $weekday],
                [
                    'opens_at' => $isClosed ? null : $this->normalizeTime($row['opens_at']),
                    'closes_at' => $isClosed ? null : $this->normalizeTime($row['closes_at']),
                    'is_closed' => $isClosed,
                ],
            );
        }

        return redirect()->route('schedules.edit')->with('status', 'Horarios actualizados.');
    }

    private function weekdays(): array
    {
        return [
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miercoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sabado',
            7 => 'Domingo',
        ];
    }

    private function scheduleAttributes(): array
    {
        $attributes = [];

        foreach ($this->weekdays() as $weekday => $label) {
            $attributes["schedule.{$weekday}.opens_at"] = "{$label} - hora de apertura";
            $attributes["schedule.{$weekday}.closes_at"] = "{$label} - hora de cierre";
            $attributes["schedule.{$weekday}.is_closed"] = "{$label} cerrado";
        }

        return $attributes;
    }

    private function scheduleMessages(): array
    {
        return [
            'schedule.*.opens_at.required' => ':attribute es obligatoria si el dia esta abierto.',
            'schedule.*.closes_at.required' => ':attribute es obligatoria si el dia esta abierto.',
            'schedule.*.opens_at.date_format' => ':attribute debe ser una hora valida, por ejemplo 08:00.',
            'schedule.*.closes_at.date_format' => ':attribute debe ser una hora valida, por ejemplo 18:00.',
            'schedule.*.closes_at.after' => ':attribute debe ser posterior a la hora de apertura.',
        ];
    }

    private function normalizeTime(?string $time): ?string
    {
        return $time ? substr($time, 0, 5) : null;
    }
}
