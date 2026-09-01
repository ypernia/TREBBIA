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
            'schedule.*.opens_at' => ['nullable', 'date_format:H:i'],
            'schedule.*.closes_at' => ['nullable', 'date_format:H:i'],
            'schedule.*.is_closed' => ['nullable', 'boolean'],
        ]);

        $business = app('activeBusiness');

        foreach ($this->weekdays() as $weekday => $label) {
            $row = $attributes['schedule'][$weekday] ?? [];
            $isClosed = (bool) ($row['is_closed'] ?? false);

            $request->validate([
                "schedule.{$weekday}.closes_at" => [$isClosed ? 'nullable' : 'required', 'date_format:H:i', "after:schedule.{$weekday}.opens_at"],
                "schedule.{$weekday}.opens_at" => [$isClosed ? 'nullable' : 'required', 'date_format:H:i'],
            ]);

            BusinessSchedule::updateOrCreate(
                ['business_id' => $business->id, 'branch_id' => null, 'weekday' => $weekday],
                [
                    'opens_at' => $isClosed ? null : $row['opens_at'],
                    'closes_at' => $isClosed ? null : $row['closes_at'],
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
}
