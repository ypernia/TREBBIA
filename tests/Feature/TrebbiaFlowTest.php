<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\Business;
use App\Models\BusinessSchedule;
use App\Models\BusinessUser;
use App\Models\Client;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\Resource;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrebbiaFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_create_business_complete_onboarding_and_open_dashboard(): void
    {
        $this->post(route('register.store'), [
            'name' => 'Laura Perez',
            'email' => 'laura@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('business.create'));

        $this->post(route('business.store'), [
            'name' => 'Trebbia Demo',
            'industry' => 'Fisioterapia',
            'email' => 'hola@demo.test',
            'phone' => '3001234567',
            'timezone' => 'America/Bogota',
        ])->assertRedirect(route('onboarding.show', ['step' => 'negocio']));

        $business = Business::first();

        $this->assertNotNull($business);
        $this->assertDatabaseHas('business_users', [
            'business_id' => $business->id,
            'role' => BusinessUser::ROLE_OWNER,
        ]);

        $this->withSession(['business_id' => $business->id])
            ->post(route('onboarding.store', 'negocio'), [
                'name' => 'Trebbia Demo',
                'industry' => 'Fisioterapia',
                'email' => 'hola@demo.test',
                'phone' => '3001234567',
            ])->assertRedirect(route('onboarding.show', ['step' => 'horarios']));

        $this->withSession(['business_id' => $business->id])
            ->post(route('onboarding.store', 'horarios'), [
                'opens_at' => '08:00',
                'closes_at' => '18:00',
                'weekdays' => [1, 2, 3, 4, 5],
            ])->assertRedirect(route('onboarding.show', ['step' => 'servicio']));

        $this->withSession(['business_id' => $business->id])
            ->post(route('onboarding.store', 'servicio'), [
                'name' => 'Consulta inicial',
                'duration_minutes' => 60,
                'price' => 120000,
                'description' => 'Primera valoracion.',
            ])->assertRedirect(route('onboarding.show', ['step' => 'profesional']));

        $this->withSession(['business_id' => $business->id])
            ->post(route('onboarding.store', 'profesional'), [
                'name' => 'Dra. Mora',
                'title' => 'Fisioterapeuta',
                'email' => 'mora@example.com',
                'phone' => '3009876543',
            ])->assertRedirect(route('onboarding.show', ['step' => 'finalizar']));

        $this->withSession(['business_id' => $business->id])
            ->post(route('onboarding.store', 'finalizar'))
            ->assertRedirect(route('dashboard'));

        $this->assertDatabaseHas('businesses', ['id' => $business->id, 'status' => 'active']);
        $this->assertSame(1, Service::where('business_id', $business->id)->count());
        $this->assertSame(1, Professional::where('business_id', $business->id)->count());

        $this->withSession(['business_id' => $business->id])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Inicio')
            ->assertSee('Servicios activos');
    }

    public function test_authenticated_user_without_business_is_redirected_to_business_creation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('business.create'));
    }

    public function test_core_records_are_created_inside_the_active_business(): void
    {
        [$user, $business] = $this->tenantUser();

        $this->actingAs($user)->withSession(['business_id' => $business->id]);

        $this->post(route('servicios.store'), [
            'name' => 'Terapia manual',
            'duration_minutes' => 45,
            'price' => 90000,
            'is_active' => 1,
        ])->assertRedirect(route('servicios.index'));

        $this->post(route('clientes.store'), [
            'name' => 'Ana Ruiz',
            'email' => 'ana@example.com',
            'phone' => '3001112233',
        ])->assertRedirect(route('clientes.index'));

        $this->post(route('profesionales.store'), [
            'name' => 'Carlos Rios',
            'title' => 'Terapeuta',
            'branch_id' => $business->branches()->first()->id,
            'is_active' => 1,
        ])->assertRedirect(route('profesionales.index'));

        $this->post(route('recursos.store'), [
            'name' => 'Consultorio 1',
            'type' => 'Sala',
            'branch_id' => $business->branches()->first()->id,
            'is_active' => 1,
        ])->assertRedirect(route('recursos.index'));

        $this->assertSame(1, Service::where('business_id', $business->id)->count());
        $this->assertSame(1, Client::where('business_id', $business->id)->count());
        $this->assertSame(1, Professional::where('business_id', $business->id)->count());
        $this->assertSame(1, Resource::where('business_id', $business->id)->count());
    }

    public function test_user_cannot_edit_records_from_another_business(): void
    {
        [$user, $business] = $this->tenantUser();
        [$otherUser, $otherBusiness] = $this->tenantUser('Other Clinic', 'other@example.com');
        $service = $otherBusiness->services()->create([
            'name' => 'Servicio privado',
            'duration_minutes' => 60,
            'price_cents' => 50000,
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('servicios.edit', $service))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->withSession(['business_id' => $otherBusiness->id])
            ->get(route('servicios.edit', $service))
            ->assertOk();
    }

    public function test_client_profile_shows_contact_status_and_appointment_history(): void
    {
        [$user, $business] = $this->tenantUser();
        $client = $business->clients()->create([
            'name' => 'Ana Ruiz',
            'email' => 'ana@example.com',
            'phone' => '3001112233',
            'document_number' => 'CC123',
            'birthdate' => '1990-05-10',
            'notes' => 'Prefiere citas en la manana.',
            'is_active' => true,
        ]);
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);

        $business->appointments()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => now()->addDay()->setTime(9, 0),
            'ends_at' => now()->addDay()->setTime(10, 0),
            'status' => 'confirmed',
            'notes' => 'Traer examenes.',
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('clientes.show', $client))
            ->assertOk()
            ->assertSee('Ficha')
            ->assertSee('CC123')
            ->assertSee('Prefiere citas en la manana.')
            ->assertSee('Proxima cita')
            ->assertSee('Historial de citas')
            ->assertSee('Consulta')
            ->assertSee('Dra. Mora')
            ->assertSee('Confirmada');
    }

    public function test_clients_can_be_filtered_by_document_and_status(): void
    {
        [$user, $business] = $this->tenantUser();

        $business->clients()->create([
            'name' => 'Ana Ruiz',
            'document_number' => 'CC123',
            'is_active' => true,
        ]);
        $business->clients()->create([
            'name' => 'Luis Pena',
            'document_number' => 'CC999',
            'is_active' => false,
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('clientes.index', ['q' => 'CC999', 'status' => 'inactive']))
            ->assertOk()
            ->assertSee('Luis Pena')
            ->assertDontSee('Ana Ruiz');
    }

    public function test_user_cannot_view_client_from_another_business(): void
    {
        [$user, $business] = $this->tenantUser();
        [, $otherBusiness] = $this->tenantUser('Other CRM', 'crm-owner@example.com');
        $client = $otherBusiness->clients()->create(['name' => 'Cliente Privado']);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('clientes.show', $client))
            ->assertNotFound();
    }

    public function test_business_schedule_can_be_updated(): void
    {
        [$user, $business] = $this->tenantUser();

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->put(route('schedules.update'), [
                'schedule' => [
                    1 => ['opens_at' => '08:00', 'closes_at' => '17:00', 'is_closed' => 0],
                    2 => ['opens_at' => '08:00', 'closes_at' => '17:00', 'is_closed' => 0],
                    3 => ['opens_at' => '08:00', 'closes_at' => '17:00', 'is_closed' => 0],
                    4 => ['opens_at' => '08:00', 'closes_at' => '17:00', 'is_closed' => 0],
                    5 => ['opens_at' => '08:00', 'closes_at' => '17:00', 'is_closed' => 0],
                    6 => ['opens_at' => null, 'closes_at' => null, 'is_closed' => 1],
                    7 => ['opens_at' => null, 'closes_at' => null, 'is_closed' => 1],
                ],
            ])->assertRedirect(route('schedules.edit'));

        $this->assertSame(7, BusinessSchedule::where('business_id', $business->id)->count());
        $this->assertDatabaseHas('business_schedules', [
            'business_id' => $business->id,
            'weekday' => 6,
            'is_closed' => true,
        ]);
    }

    public function test_user_can_create_appointment_for_active_business(): void
    {
        [$user, $business] = $this->tenantUser();
        $client = $business->clients()->create(['name' => 'Ana Ruiz']);
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);
        $this->openWeekday($business, 1);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->post(route('agenda.store'), [
                'client_id' => $client->id,
                'service_id' => $service->id,
                'professional_id' => $professional->id,
                'date' => '2026-09-07',
                'starts_at' => '09:00',
                'status' => 'scheduled',
            ])->assertRedirect(route('agenda.index', ['date' => '2026-09-07']));

        $this->assertDatabaseHas('appointments', [
            'business_id' => $business->id,
            'client_id' => $client->id,
            'professional_id' => $professional->id,
            'service_id' => $service->id,
            'status' => 'scheduled',
        ]);
    }

    public function test_appointment_cannot_overlap_same_professional(): void
    {
        [$user, $business] = $this->tenantUser();
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);
        $this->openWeekday($business, 1);

        $business->appointments()->create([
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => '2026-09-07 09:00:00',
            'ends_at' => '2026-09-07 10:00:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->from(route('agenda.create'))
            ->post(route('agenda.store'), [
                'service_id' => $service->id,
                'professional_id' => $professional->id,
                'date' => '2026-09-07',
                'starts_at' => '09:30',
                'status' => 'scheduled',
            ])->assertRedirect(route('agenda.create'))
            ->assertSessionHasErrors('starts_at');

        $this->assertSame(1, Appointment::where('business_id', $business->id)->count());
    }

    public function test_professional_services_and_schedule_can_be_configured(): void
    {
        [$user, $business] = $this->tenantUser();
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->post(route('profesionales.store'), [
                'name' => 'Dra. Mora',
                'title' => 'Fisioterapeuta',
                'is_active' => 1,
                'service_ids' => [$service->id],
                'schedule' => [
                    1 => ['starts_at' => '09:00', 'ends_at' => '17:00', 'is_closed' => 0],
                    2 => ['starts_at' => '09:00', 'ends_at' => '17:00', 'is_closed' => 0],
                    3 => ['starts_at' => '09:00', 'ends_at' => '17:00', 'is_closed' => 0],
                    4 => ['starts_at' => '09:00', 'ends_at' => '17:00', 'is_closed' => 0],
                    5 => ['starts_at' => '09:00', 'ends_at' => '17:00', 'is_closed' => 0],
                    6 => ['starts_at' => '09:00', 'ends_at' => '12:00', 'is_closed' => 1],
                    7 => ['starts_at' => '09:00', 'ends_at' => '12:00', 'is_closed' => 1],
                ],
            ])->assertRedirect(route('profesionales.index'));

        $professional = Professional::where('business_id', $business->id)->where('name', 'Dra. Mora')->firstOrFail();

        $this->assertDatabaseHas('professional_service', [
            'business_id' => $business->id,
            'professional_id' => $professional->id,
            'service_id' => $service->id,
        ]);
        $this->assertDatabaseHas('professional_schedules', [
            'business_id' => $business->id,
            'professional_id' => $professional->id,
            'weekday' => 1,
            'starts_at' => '09:00',
            'ends_at' => '17:00',
            'is_closed' => false,
        ]);
    }

    public function test_appointment_requires_professional_service_match_when_service_has_assignments(): void
    {
        [$user, $business] = $this->tenantUser();
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);
        $otherProfessional = $business->professionals()->create(['name' => 'Carlos Rios', 'is_active' => true]);
        $service->professionals()->syncWithPivotValues([$professional->id], ['business_id' => $business->id]);
        $this->openWeekday($business, 1);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->from(route('agenda.create'))
            ->post(route('agenda.store'), [
                'service_id' => $service->id,
                'professional_id' => $otherProfessional->id,
                'date' => '2026-09-07',
                'starts_at' => '09:00',
                'status' => 'scheduled',
            ])->assertRedirect(route('agenda.create'))
            ->assertSessionHasErrors('starts_at');

        $this->assertSame(0, Appointment::where('business_id', $business->id)->count());
    }

    public function test_appointment_respects_professional_schedule(): void
    {
        [$user, $business] = $this->tenantUser();
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);
        $service->professionals()->syncWithPivotValues([$professional->id], ['business_id' => $business->id]);
        $this->openWeekday($business, 1);
        ProfessionalSchedule::create([
            'business_id' => $business->id,
            'professional_id' => $professional->id,
            'weekday' => 1,
            'starts_at' => '10:00',
            'ends_at' => '12:00',
            'is_closed' => false,
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->from(route('agenda.create'))
            ->post(route('agenda.store'), [
                'service_id' => $service->id,
                'professional_id' => $professional->id,
                'date' => '2026-09-07',
                'starts_at' => '09:00',
                'status' => 'scheduled',
            ])->assertRedirect(route('agenda.create'))
            ->assertSessionHasErrors('starts_at');

        $this->assertSame(0, Appointment::where('business_id', $business->id)->count());
    }

    public function test_user_cannot_edit_appointment_from_another_business(): void
    {
        [$user, $business] = $this->tenantUser();
        [, $otherBusiness] = $this->tenantUser('Other Agenda', 'agenda-owner@example.com');
        $appointment = $otherBusiness->appointments()->create([
            'starts_at' => '2026-09-07 09:00:00',
            'ends_at' => '2026-09-07 10:00:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('agenda.edit', $appointment))
            ->assertNotFound();
    }

    public function test_agenda_supports_week_view_and_filters(): void
    {
        [$user, $business] = $this->tenantUser();
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $otherService = $business->services()->create(['name' => 'Masaje', 'duration_minutes' => 30, 'price_cents' => 50000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);
        $otherProfessional = $business->professionals()->create(['name' => 'Carlos Rios', 'is_active' => true]);

        $business->appointments()->create([
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => '2026-09-07 09:00:00',
            'ends_at' => '2026-09-07 10:00:00',
            'status' => 'confirmed',
        ]);
        $business->appointments()->create([
            'service_id' => $otherService->id,
            'professional_id' => $otherProfessional->id,
            'starts_at' => '2026-09-08 15:00:00',
            'ends_at' => '2026-09-08 15:30:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('agenda.index', [
                'view' => 'week',
                'date' => '2026-09-07',
                'professional_id' => $professional->id,
                'service_id' => $service->id,
                'status' => 'confirmed',
            ]))
            ->assertOk()
            ->assertSee('Semana del 07/09/2026')
            ->assertSee('Consulta')
            ->assertSee('Dra. Mora')
            ->assertDontSee('15:00 - 15:30');
    }

    public function test_appointment_form_previews_availability_conflict(): void
    {
        [$user, $business] = $this->tenantUser();
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);
        $this->openWeekday($business, 1);

        $business->appointments()->create([
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => '2026-09-07 09:00:00',
            'ends_at' => '2026-09-07 10:00:00',
            'status' => 'scheduled',
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('agenda.create', [
                'date' => '2026-09-07',
                'starts_at' => '09:30',
                'service_id' => $service->id,
                'professional_id' => $professional->id,
            ]))
            ->assertOk()
            ->assertSee('Disponibilidad por revisar')
            ->assertSee('El profesional ya tiene una cita en ese horario.');
    }

    public function test_automations_dashboard_creates_default_template(): void
    {
        [$user, $business] = $this->tenantUser();

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('automations.index'))
            ->assertOk()
            ->assertSee('Automatizaciones')
            ->assertSee('Recordatorio de cita');

        $this->assertDatabaseHas('notification_templates', [
            'business_id' => $business->id,
            'name' => 'Recordatorio de cita',
            'trigger' => 'appointment_reminder',
        ]);
    }

    public function test_reminder_can_be_scheduled_and_marked_sent(): void
    {
        [$user, $business] = $this->tenantUser();
        $client = $business->clients()->create(['name' => 'Ana Ruiz']);
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);
        $appointment = $business->appointments()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => now()->addDays(2)->setTime(9, 0),
            'ends_at' => now()->addDays(2)->setTime(10, 0),
            'status' => 'confirmed',
        ]);
        $template = $business->notificationTemplates()->create([
            'name' => 'WhatsApp 24 horas',
            'channel' => 'whatsapp',
            'trigger' => 'appointment_reminder',
            'body' => 'Hola {cliente}, tu cita de {servicio} es el {fecha} a las {hora}.',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->post(route('automations.reminders.schedule', $appointment), [
                'notification_template_id' => $template->id,
                'channel' => 'whatsapp',
                'scheduled_for' => now()->addDay()->setTime(9, 0)->format('Y-m-d H:i:s'),
            ])->assertRedirect(route('automations.index'));

        $reminder = AppointmentReminder::where('business_id', $business->id)->firstOrFail();
        $this->assertSame(AppointmentReminder::STATUS_PENDING, $reminder->status);
        $this->assertStringContainsString('Ana Ruiz', $reminder->message_snapshot);
        $this->assertStringContainsString('Consulta', $reminder->message_snapshot);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->patch(route('automations.reminders.sent', $reminder))
            ->assertRedirect(route('automations.index'));

        $this->assertDatabaseHas('appointment_reminders', [
            'id' => $reminder->id,
            'status' => AppointmentReminder::STATUS_SENT,
        ]);
        $this->assertNotNull($reminder->fresh()->sent_at);
    }

    public function test_user_cannot_schedule_reminder_for_other_business_appointment(): void
    {
        [$user, $business] = $this->tenantUser();
        [, $otherBusiness] = $this->tenantUser('Other Automation', 'automation-owner@example.com');
        $appointment = $otherBusiness->appointments()->create([
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDay()->addHour(),
            'status' => 'scheduled',
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->post(route('automations.reminders.schedule', $appointment), [
                'channel' => 'manual',
                'scheduled_for' => now()->format('Y-m-d H:i:s'),
            ])->assertNotFound();
    }

    private function tenantUser(string $businessName = 'Clinica Demo', string $email = 'owner@example.com'): array
    {
        $user = User::factory()->create(['email' => $email]);

        $this->actingAs($user)->post(route('business.store'), [
            'name' => $businessName,
            'industry' => 'Servicios',
            'timezone' => 'America/Bogota',
        ]);

        return [$user, Business::where('name', $businessName)->firstOrFail()];
    }

    private function openWeekday(Business $business, int $weekday): void
    {
        BusinessSchedule::updateOrCreate(
            ['business_id' => $business->id, 'branch_id' => null, 'weekday' => $weekday],
            ['opens_at' => '08:00', 'closes_at' => '18:00', 'is_closed' => false],
        );
    }
}
