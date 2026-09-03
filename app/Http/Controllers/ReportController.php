<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __invoke(Request $request): View
    {
        $business = app('activeBusiness');
        $startDate = CarbonImmutable::parse($request->input('start_date', now()->startOfMonth()->toDateString()), $business->timezone)->startOfDay();
        $endDate = CarbonImmutable::parse($request->input('end_date', now()->endOfMonth()->toDateString()), $business->timezone)->endOfDay();

        if ($endDate->lessThan($startDate)) {
            [$startDate, $endDate] = [$endDate->startOfDay(), $startDate->endOfDay()];
        }

        $appointments = $business->appointments()
            ->with(['client', 'professional', 'service'])
            ->whereBetween('starts_at', [$startDate, $endDate])
            ->orderBy('starts_at')
            ->get();

        $completedAppointments = $appointments->where('status', 'completed');
        $estimatedRevenueCents = $completedAppointments->sum(fn (Appointment $appointment): int => $appointment->service?->price_cents ?? 0);

        return view('reports.index', [
            'business' => $business,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'metrics' => [
                'appointments' => $appointments->count(),
                'completed' => $completedAppointments->count(),
                'cancelled' => $appointments->where('status', 'cancelled')->count(),
                'estimatedRevenueCents' => $estimatedRevenueCents,
                'averageTicketCents' => $completedAppointments->count() > 0 ? (int) round($estimatedRevenueCents / $completedAppointments->count()) : 0,
                'newClients' => $business->clients()->whereBetween('created_at', [$startDate, $endDate])->count(),
            ],
            'appointmentsByStatus' => $this->appointmentsByStatus($appointments),
            'topServices' => $this->topServices($appointments),
            'topProfessionals' => $this->topProfessionals($appointments),
            'recentClients' => $business->clients()->latest()->take(6)->get(),
            'upcomingAppointments' => $business->appointments()
                ->with(['client', 'professional', 'service'])
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->take(6)
                ->get(),
        ]);
    }

    private function appointmentsByStatus(Collection $appointments): array
    {
        $labels = [
            'scheduled' => 'Programadas',
            'confirmed' => 'Confirmadas',
            'cancelled' => 'Canceladas',
            'completed' => 'Completadas',
        ];

        return collect($labels)
            ->map(fn (string $label, string $status): array => [
                'label' => $label,
                'count' => $appointments->where('status', $status)->count(),
            ])
            ->values()
            ->all();
    }

    private function topServices(Collection $appointments): Collection
    {
        return $appointments
            ->filter(fn (Appointment $appointment): bool => $appointment->service !== null)
            ->groupBy('service_id')
            ->map(fn (Collection $items): array => [
                'name' => $items->first()->service->name,
                'count' => $items->count(),
                'revenue_cents' => $items->where('status', 'completed')->sum(fn (Appointment $appointment): int => $appointment->service?->price_cents ?? 0),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values();
    }

    private function topProfessionals(Collection $appointments): Collection
    {
        return $appointments
            ->filter(fn (Appointment $appointment): bool => $appointment->professional !== null)
            ->groupBy('professional_id')
            ->map(fn (Collection $items): array => [
                'name' => $items->first()->professional->name,
                'count' => $items->count(),
                'completed' => $items->where('status', 'completed')->count(),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values();
    }
}
