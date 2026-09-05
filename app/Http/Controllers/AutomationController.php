<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\NotificationTemplate;
use App\Services\PlanEntitlements;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AutomationController extends Controller
{
    public function index(): View
    {
        $business = app('activeBusiness');
        abort_unless(app(PlanEntitlements::class)->can($business, 'automation.enabled'), 403);
        $this->ensureDefaultTemplate();

        return view('automations.index', [
            'business' => $business,
            'templates' => $business->notificationTemplates()->latest()->get(),
            'pendingAppointments' => $business->appointments()
                ->with(['client', 'professional', 'service'])
                ->whereIn('status', ['scheduled', 'confirmed'])
                ->where('starts_at', '>=', now())
                ->whereDoesntHave('reminders', fn ($query) => $query->whereIn('status', [AppointmentReminder::STATUS_PENDING, AppointmentReminder::STATUS_SENT]))
                ->orderBy('starts_at')
                ->take(10)
                ->get(),
            'reminders' => $business->appointmentReminders()
                ->with(['appointment.client', 'appointment.professional', 'appointment.service', 'template'])
                ->latest('scheduled_for')
                ->paginate(10),
            'pendingCount' => $business->appointmentReminders()->where('status', AppointmentReminder::STATUS_PENDING)->count(),
            'sentCount' => $business->appointmentReminders()->where('status', AppointmentReminder::STATUS_SENT)->count(),
            'skippedCount' => $business->appointmentReminders()->where('status', AppointmentReminder::STATUS_SKIPPED)->count(),
        ]);
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        abort_unless(app(PlanEntitlements::class)->can(app('activeBusiness'), 'automation.enabled'), 403);
        app('activeBusiness')->notificationTemplates()->create($this->validatedTemplate($request));

        return redirect()->route('automations.index')->with('status', 'Plantilla creada.');
    }

    public function updateTemplate(Request $request, NotificationTemplate $template): RedirectResponse
    {
        abort_unless(app(PlanEntitlements::class)->can(app('activeBusiness'), 'automation.enabled'), 403);
        $this->authorizeTemplate($template);
        $template->update($this->validatedTemplate($request));

        return redirect()->route('automations.index')->with('status', 'Plantilla actualizada.');
    }

    public function scheduleReminder(Request $request, Appointment $appointment): RedirectResponse
    {
        abort_unless(app(PlanEntitlements::class)->can(app('activeBusiness'), 'automation.enabled'), 403);
        $this->authorizeAppointment($appointment);
        $business = app('activeBusiness');

        $attributes = $request->validate([
            'notification_template_id' => ['nullable', Rule::exists('notification_templates', 'id')->where('business_id', $business->id)],
            'channel' => ['required', Rule::in(['manual', 'email', 'whatsapp'])],
            'scheduled_for' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:800'],
        ]);

        $template = $attributes['notification_template_id']
            ? $business->notificationTemplates()->find($attributes['notification_template_id'])
            : $this->ensureDefaultTemplate();

        $business->appointmentReminders()->create([
            'appointment_id' => $appointment->id,
            'notification_template_id' => $template?->id,
            'channel' => $attributes['channel'],
            'scheduled_for' => $attributes['scheduled_for'],
            'status' => AppointmentReminder::STATUS_PENDING,
            'message_snapshot' => $this->renderMessage($appointment, $template),
            'notes' => $attributes['notes'] ?? null,
        ]);

        return redirect()->route('automations.index')->with('status', 'Recordatorio programado.');
    }

    public function markReminderSent(AppointmentReminder $reminder): RedirectResponse
    {
        $this->authorizeReminder($reminder);
        $reminder->update([
            'status' => AppointmentReminder::STATUS_SENT,
            'sent_at' => now(),
        ]);

        return redirect()->route('automations.index')->with('status', 'Recordatorio marcado como enviado.');
    }

    public function skipReminder(AppointmentReminder $reminder): RedirectResponse
    {
        $this->authorizeReminder($reminder);
        $reminder->update([
            'status' => AppointmentReminder::STATUS_SKIPPED,
        ]);

        return redirect()->route('automations.index')->with('status', 'Recordatorio omitido.');
    }

    private function ensureDefaultTemplate(): NotificationTemplate
    {
        return app('activeBusiness')->notificationTemplates()->firstOrCreate(
            ['trigger' => 'appointment_reminder', 'name' => 'Recordatorio de cita'],
            [
                'channel' => 'manual',
                'subject' => 'Recordatorio de cita',
                'body' => 'Hola {cliente}, te recordamos tu cita de {servicio} el {fecha} a las {hora}.',
                'is_active' => true,
            ],
        );
    }

    private function validatedTemplate(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:140'],
            'channel' => ['required', Rule::in(['manual', 'email', 'whatsapp'])],
            'trigger' => ['required', Rule::in(['appointment_reminder', 'appointment_confirmation'])],
            'subject' => ['nullable', 'string', 'max:180'],
            'body' => ['required', 'string', 'max:1600'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function renderMessage(Appointment $appointment, ?NotificationTemplate $template): string
    {
        $body = $template?->body ?: 'Hola {cliente}, te recordamos tu cita de {servicio} el {fecha} a las {hora}.';
        $appointment->loadMissing(['client', 'professional', 'service']);

        return str($body)
            ->replace('{cliente}', $appointment->client?->name ?: 'cliente')
            ->replace('{servicio}', $appointment->service?->name ?: 'servicio')
            ->replace('{profesional}', $appointment->professional?->name ?: 'profesional')
            ->replace('{fecha}', $appointment->starts_at->format('d/m/Y'))
            ->replace('{hora}', $appointment->starts_at->format('H:i'))
            ->toString();
    }

    private function authorizeAppointment(Appointment $appointment): void
    {
        abort_unless($appointment->business_id === app('activeBusiness')->id, 404);
    }

    private function authorizeReminder(AppointmentReminder $reminder): void
    {
        abort_unless($reminder->business_id === app('activeBusiness')->id, 404);
    }

    private function authorizeTemplate(NotificationTemplate $template): void
    {
        abort_unless($template->business_id === app('activeBusiness')->id, 404);
    }
}
