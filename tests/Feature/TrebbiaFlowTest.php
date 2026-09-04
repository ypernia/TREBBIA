<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\BlockedTime;
use App\Models\Business;
use App\Models\BusinessInvitation;
use App\Models\BusinessSchedule;
use App\Models\BusinessUser;
use App\Models\Client;
use App\Models\Plan;
use App\Models\Professional;
use App\Models\ProfessionalSchedule;
use App\Models\Resource;
use App\Models\Service;
use App\Models\User;
use App\Models\WhatsAppAccount;
use App\Services\BookingEngine;
use App\Services\ConversationManager;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
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

    public function test_resource_suggestions_match_business_industry_and_can_be_created_in_bulk(): void
    {
        [$user, $business] = $this->tenantUser();
        $business->update(['industry' => 'Fisioterapia']);
        $branch = $business->branches()->firstOrFail();

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('recursos.index'))
            ->assertOk()
            ->assertSee('Consultorio fisioterapia 1')
            ->assertSee('Camilla 1')
            ->assertSee('Equipo de electroterapia');

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->post(route('recursos.suggestions.store'), [
                'branch_id' => $branch->id,
                'resources' => [
                    ['name' => 'Consultorio fisioterapia 1', 'type' => 'Consultorio', 'selected' => 1],
                    ['name' => 'Camilla 1', 'type' => 'Equipo', 'selected' => 1],
                    ['name' => 'Camilla 2', 'type' => 'Equipo', 'selected' => 0],
                ],
            ])->assertRedirect(route('recursos.index'));

        $this->assertDatabaseHas('resources', [
            'business_id' => $business->id,
            'branch_id' => $branch->id,
            'name' => 'Consultorio fisioterapia 1',
            'type' => 'Consultorio',
        ]);
        $this->assertDatabaseHas('resources', [
            'business_id' => $business->id,
            'name' => 'Camilla 1',
        ]);
        $this->assertDatabaseMissing('resources', [
            'business_id' => $business->id,
            'name' => 'Camilla 2',
        ]);
    }

    public function test_resource_suggestion_bulk_creation_skips_existing_names(): void
    {
        [$user, $business] = $this->tenantUser();
        $business->resources()->create(['name' => 'Sala 1', 'type' => 'Sala', 'is_active' => true]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->post(route('recursos.suggestions.store'), [
                'resources' => [
                    ['name' => 'Sala 1', 'type' => 'Sala', 'selected' => 1],
                    ['name' => 'Consultorio 1', 'type' => 'Consultorio', 'selected' => 1],
                ],
            ])->assertRedirect(route('recursos.index'));

        $this->assertSame(1, Resource::where('business_id', $business->id)->where('name', 'Sala 1')->count());
        $this->assertDatabaseHas('resources', [
            'business_id' => $business->id,
            'name' => 'Consultorio 1',
        ]);
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

    public function test_business_settings_profile_preferences_and_branches_can_be_updated(): void
    {
        [$user, $business] = $this->tenantUser();
        $branch = $business->branches()->firstOrFail();

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->put(route('settings.business.update'), [
                'name' => 'TREBBIA Centro',
                'industry' => 'Salud',
                'email' => 'hola@trebbia.test',
                'phone' => '3001234567',
                'timezone' => 'America/Bogota',
                'currency' => 'USD',
            ])->assertRedirect(route('settings.index'));

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->put(route('settings.preferences.update'), [
                'slot_interval_minutes' => 15,
                'booking_notice_minutes' => 240,
                'allow_public_booking' => 1,
                'require_manual_confirmation' => 1,
                'notify_email' => 1,
                'notify_whatsapp' => 0,
            ])->assertRedirect(route('settings.index'));

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->post(route('settings.branches.store'), [
                'name' => 'Sede norte',
                'phone' => '3010000000',
                'address' => 'Calle 10',
                'is_main' => 1,
                'is_active' => 1,
            ])->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('businesses', [
            'id' => $business->id,
            'name' => 'TREBBIA Centro',
            'currency' => 'USD',
        ]);
        $this->assertDatabaseHas('business_settings', [
            'business_id' => $business->id,
            'slot_interval_minutes' => 15,
            'booking_notice_minutes' => 240,
        ]);
        $this->assertDatabaseHas('branches', [
            'business_id' => $business->id,
            'name' => 'Sede norte',
            'is_main' => true,
        ]);
        $this->assertDatabaseHas('branches', [
            'id' => $branch->id,
            'is_main' => false,
        ]);
    }

    public function test_settings_page_shows_users_and_business_summary(): void
    {
        [$user, $business] = $this->tenantUser();

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('settings.index'))
            ->assertOk()
            ->assertSee('Perfil del negocio')
            ->assertSee('Preferencias de agenda')
            ->assertSee('Canal WhatsApp')
            ->assertSee('Sedes')
            ->assertSee('Usuarios y roles')
            ->assertSee($user->email);
    }

    public function test_whatsapp_channel_settings_can_be_updated(): void
    {
        [$user, $business] = $this->tenantUser();

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->put(route('settings.whatsapp.update'), [
                'enabled' => 1,
                'phone' => '+57 311 330 2090',
                'display_name' => 'TREBBIA Salud',
                'entry_message' => 'Hola, quiero agendar una cita',
                'welcome_message' => 'Bienvenido a TREBBIA Salud.',
                'unavailable_message' => 'No hay horarios para esa opcion.',
                'confirmation_message' => 'Tu cita quedo lista.',
                'appointment_status' => 'confirmed',
                'phone_number_id' => '1234567890',
                'waba_id' => '9876543210',
                'access_token' => 'EAAB_demo_token',
            ])
            ->assertRedirect(route('settings.index'));

        $business->refresh();
        $settings = $business->settings()->firstOrFail()->whatsapp_settings;

        $this->assertTrue($settings['enabled']);
        $this->assertSame('573113302090', $settings['phone']);
        $this->assertSame('confirmed', $settings['appointment_status']);
        $this->assertSame('simulated', $settings['status']);
        $this->assertDatabaseHas('whatsapp_accounts', [
            'business_id' => $business->id,
            'phone_number_id' => '1234567890',
            'waba_id' => '9876543210',
            'is_active' => true,
        ]);
    }

    public function test_team_invitation_creates_pending_invitation_for_new_email(): void
    {
        [$user, $business] = $this->tenantUser();
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->post(route('team.invitations.store'), [
                'name' => 'Recepcion Demo',
                'email' => 'recepcion@example.com',
                'role' => BusinessUser::ROLE_RECEPTIONIST,
                'professional_id' => $professional->id,
            ])->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('business_invitations', [
            'business_id' => $business->id,
            'email' => 'recepcion@example.com',
            'role' => BusinessUser::ROLE_RECEPTIONIST,
            'status' => BusinessInvitation::STATUS_PENDING,
        ]);
    }

    public function test_existing_user_can_be_added_to_business_and_linked_to_professional(): void
    {
        [$user, $business] = $this->tenantUser();
        $existingUser = User::factory()->create(['email' => 'pro@example.com']);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->post(route('team.invitations.store'), [
                'email' => $existingUser->email,
                'role' => BusinessUser::ROLE_PROFESSIONAL,
                'professional_id' => $professional->id,
            ])->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('business_users', [
            'business_id' => $business->id,
            'user_id' => $existingUser->id,
            'role' => BusinessUser::ROLE_PROFESSIONAL,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('professionals', [
            'id' => $professional->id,
            'user_id' => $existingUser->id,
        ]);
    }

    public function test_team_member_role_and_status_can_be_updated(): void
    {
        [$user, $business] = $this->tenantUser();
        $memberUser = User::factory()->create(['email' => 'admin@example.com']);
        $member = BusinessUser::create([
            'business_id' => $business->id,
            'user_id' => $memberUser->id,
            'role' => BusinessUser::ROLE_RECEPTIONIST,
            'permissions' => BusinessUser::ROLE_PERMISSIONS[BusinessUser::ROLE_RECEPTIONIST],
            'is_active' => true,
            'joined_at' => now(),
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->patch(route('team.members.update', $member), [
                'role' => BusinessUser::ROLE_ADMIN,
                'is_active' => 0,
            ])->assertRedirect(route('settings.index'));

        $this->assertDatabaseHas('business_users', [
            'id' => $member->id,
            'role' => BusinessUser::ROLE_ADMIN,
            'is_active' => false,
        ]);
    }

    public function test_team_invitation_respects_user_limit(): void
    {
        [$user, $business] = $this->tenantUser();
        $plan = Plan::create([
            'name' => 'Limitado',
            'code' => 'limited',
            'monthly_price_cents' => 0,
            'limits' => ['users' => 1],
            'features' => [],
            'is_active' => true,
        ]);
        $business->subscription()->create([
            'plan_id' => $plan->id,
            'status' => 'active',
            'current_period_ends_at' => now()->addMonth(),
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->from(route('settings.index'))
            ->post(route('team.invitations.store'), [
                'email' => 'blocked@example.com',
                'role' => BusinessUser::ROLE_ADMIN,
            ])->assertRedirect(route('settings.index'))
            ->assertSessionHasErrors('email');

        $this->assertDatabaseMissing('business_invitations', [
            'business_id' => $business->id,
            'email' => 'blocked@example.com',
        ]);
    }

    public function test_appointment_respects_configured_slot_interval(): void
    {
        [$user, $business] = $this->tenantUser();
        $business->settings()->firstOrCreate([])->update(['slot_interval_minutes' => 30, 'booking_notice_minutes' => 0]);
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);
        $this->openWeekday($business, 1);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->from(route('agenda.create'))
            ->post(route('agenda.store'), [
                'service_id' => $service->id,
                'professional_id' => $professional->id,
                'date' => '2026-09-07',
                'starts_at' => '09:15',
                'status' => 'scheduled',
            ])->assertRedirect(route('agenda.create'))
            ->assertSessionHasErrors('starts_at');

        $this->assertSame(0, Appointment::where('business_id', $business->id)->count());
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
            'source_channel' => Appointment::SOURCE_INTERNAL,
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

    public function test_reports_show_operational_metrics_for_selected_range(): void
    {
        [$user, $business] = $this->tenantUser();
        $client = $business->clients()->create(['name' => 'Ana Ruiz']);
        $service = $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 12000000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);

        $business->appointments()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => '2026-09-10 09:00:00',
            'ends_at' => '2026-09-10 10:00:00',
            'status' => 'completed',
        ]);
        $business->appointments()->create([
            'client_id' => $client->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => '2026-09-11 09:00:00',
            'ends_at' => '2026-09-11 10:00:00',
            'status' => 'cancelled',
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('reports.index', [
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-30',
            ]))
            ->assertOk()
            ->assertSee('Reportes')
            ->assertSee('Ingresos estimados')
            ->assertSee('$120.000')
            ->assertSee('Consulta')
            ->assertSee('Dra. Mora')
            ->assertSee('Completadas')
            ->assertSee('Canceladas');
    }

    public function test_reports_only_include_active_business_data(): void
    {
        [$user, $business] = $this->tenantUser();
        [, $otherBusiness] = $this->tenantUser('Other Reports', 'reports-owner@example.com');
        $service = $business->services()->create(['name' => 'Consulta propia', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Propia', 'is_active' => true]);
        $otherService = $otherBusiness->services()->create(['name' => 'Servicio ajeno', 'duration_minutes' => 60, 'price_cents' => 90000, 'is_active' => true]);
        $otherProfessional = $otherBusiness->professionals()->create(['name' => 'Dr. Ajeno', 'is_active' => true]);

        $business->appointments()->create([
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => '2026-09-10 09:00:00',
            'ends_at' => '2026-09-10 10:00:00',
            'status' => 'completed',
        ]);
        $otherBusiness->appointments()->create([
            'service_id' => $otherService->id,
            'professional_id' => $otherProfessional->id,
            'starts_at' => '2026-09-10 09:00:00',
            'ends_at' => '2026-09-10 10:00:00',
            'status' => 'completed',
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('reports.index', [
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-30',
            ]))
            ->assertOk()
            ->assertSee('Consulta propia')
            ->assertSee('Dra. Propia')
            ->assertDontSee('Servicio ajeno')
            ->assertDontSee('Dr. Ajeno');
    }

    public function test_membership_dashboard_creates_plans_and_shows_usage(): void
    {
        [$user, $business] = $this->tenantUser();
        $business->services()->create(['name' => 'Consulta', 'duration_minutes' => 60, 'price_cents' => 80000, 'is_active' => true]);
        $business->professionals()->create(['name' => 'Dra. Mora', 'is_active' => true]);
        $business->appointments()->create([
            'starts_at' => now()->startOfMonth()->addDay()->setTime(9, 0),
            'ends_at' => now()->startOfMonth()->addDay()->setTime(10, 0),
            'status' => 'scheduled',
        ]);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('membership.index'))
            ->assertOk()
            ->assertSee('Membresia')
            ->assertSee('Inicial')
            ->assertSee('Profesional')
            ->assertSee('Citas mensuales')
            ->assertSee('1 / 50');

        $this->assertDatabaseHas('plans', ['code' => 'starter']);
        $this->assertDatabaseHas('subscriptions', [
            'business_id' => $business->id,
            'status' => 'trialing',
        ]);
    }

    public function test_membership_plan_can_be_changed_manually(): void
    {
        [$user, $business] = $this->tenantUser();

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('membership.index'))
            ->assertOk();

        $plan = Plan::where('code', 'professional')->firstOrFail();

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->put(route('membership.update'), [
                'plan_id' => $plan->id,
                'status' => 'active',
            ])->assertRedirect(route('membership.index'));

        $this->assertDatabaseHas('subscriptions', [
            'business_id' => $business->id,
            'plan_id' => $plan->id,
            'status' => 'active',
        ]);
    }

    public function test_public_booking_page_requires_enabled_public_booking(): void
    {
        [, $business] = $this->tenantUser();
        $business->update(['status' => 'active']);
        $business->settings()->firstOrCreate([])->update([
            'public_booking_settings' => ['allow_public_booking' => false],
        ]);

        $this->get(route('public-booking.show', $business->slug))
            ->assertNotFound();
    }

    public function test_public_booking_page_shows_whatsapp_entry_point_when_enabled(): void
    {
        [, $business] = $this->tenantUser();
        $business->update(['status' => 'active']);
        $business->settings()->firstOrCreate([])->update([
            'public_booking_settings' => ['allow_public_booking' => true],
            'whatsapp_settings' => [
                'enabled' => true,
                'phone' => '573113302090',
                'entry_message' => 'Hola, quiero agendar una cita',
            ],
        ]);

        $this->get(route('public-booking.show', $business->slug))
            ->assertOk()
            ->assertSee('Reservar por WhatsApp')
            ->assertSee('https://wa.me/573113302090', false);
    }

    public function test_public_booking_creates_client_and_appointment(): void
    {
        [, $business] = $this->tenantUser();
        $business->update(['status' => 'active']);
        $business->settings()->firstOrCreate([])->update([
            'slot_interval_minutes' => 30,
            'booking_notice_minutes' => 0,
            'public_booking_settings' => [
                'allow_public_booking' => true,
                'require_manual_confirmation' => true,
            ],
        ]);
        $service = $business->services()->create(['name' => 'Consulta publica', 'duration_minutes' => 60, 'price_cents' => 12000000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Publica', 'is_active' => true]);
        $service->professionals()->syncWithPivotValues([$professional->id], ['business_id' => $business->id]);
        $this->openWeekday($business, 1);

        $this->get(route('public-booking.show', [
            $business->slug,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'date' => '2026-09-07',
        ]))
            ->assertOk()
            ->assertSee('Consulta publica')
            ->assertSee('Dra. Publica')
            ->assertSee('professionalOptionsByService', false)
            ->assertSee('09:00');

        $this->post(route('public-booking.store', $business->slug), [
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'date' => '2026-09-07',
            'starts_at' => '09:00',
            'client_name' => 'Cliente Web',
            'client_email' => 'clienteweb@example.com',
            'client_phone' => '3004445566',
            'notes' => 'Primera reserva desde la web.',
        ])->assertRedirect();

        $this->assertDatabaseHas('clients', [
            'business_id' => $business->id,
            'name' => 'Cliente Web',
            'email' => 'clienteweb@example.com',
        ]);
        $this->assertDatabaseHas('appointments', [
            'business_id' => $business->id,
            'professional_id' => $professional->id,
            'service_id' => $service->id,
            'status' => 'scheduled',
            'source_channel' => Appointment::SOURCE_PUBLIC_BOOKING,
        ]);
    }

    public function test_public_booking_can_confirm_automatically_from_settings(): void
    {
        [, $business] = $this->tenantUser();
        $business->update(['status' => 'active']);
        $business->settings()->firstOrCreate([])->update([
            'slot_interval_minutes' => 30,
            'booking_notice_minutes' => 0,
            'public_booking_settings' => [
                'allow_public_booking' => true,
                'require_manual_confirmation' => false,
            ],
        ]);
        $service = $business->services()->create(['name' => 'Consulta automatica', 'duration_minutes' => 60, 'price_cents' => 9000000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dr. Automatico', 'is_active' => true]);
        $this->openWeekday($business, 1);

        $this->post(route('public-booking.store', $business->slug), [
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'date' => '2026-09-07',
            'starts_at' => '10:00',
            'client_name' => 'Cliente Confirmado',
            'client_email' => 'confirmado@example.com',
        ])->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'business_id' => $business->id,
            'status' => 'confirmed',
        ]);
    }

    public function test_booking_engine_returns_available_slots_and_respects_blocked_times(): void
    {
        [, $business] = $this->tenantUser();
        $business->settings()->firstOrCreate([])->update([
            'slot_interval_minutes' => 30,
            'booking_notice_minutes' => 0,
        ]);
        $service = $business->services()->create(['name' => 'Reserva central', 'duration_minutes' => 60, 'price_cents' => 9000000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dra. Motor', 'is_active' => true]);
        $this->openWeekday($business, 1);

        BlockedTime::create([
            'business_id' => $business->id,
            'professional_id' => $professional->id,
            'starts_at' => '2026-09-07 09:00:00',
            'ends_at' => '2026-09-07 10:00:00',
            'reason' => 'Reunion interna',
        ]);

        $engine = app(BookingEngine::class);
        $slots = $engine->availableSlots($business, $service, $professional->id, CarbonImmutable::parse('2026-09-07', $business->timezone));

        $this->assertNotContains('09:00', $slots->map->format('H:i')->all());
        $this->assertContains('10:00', $slots->map->format('H:i')->all());
    }

    public function test_booking_engine_prevents_creating_appointment_on_blocked_time(): void
    {
        [, $business] = $this->tenantUser();
        $business->settings()->firstOrCreate([])->update(['booking_notice_minutes' => 0]);
        $service = $business->services()->create(['name' => 'Reserva bloqueada', 'duration_minutes' => 60, 'price_cents' => 9000000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Dr. Bloqueo', 'is_active' => true]);
        $this->openWeekday($business, 1);

        BlockedTime::create([
            'business_id' => $business->id,
            'starts_at' => '2026-09-07 11:00:00',
            'ends_at' => '2026-09-07 12:00:00',
            'reason' => 'Bloqueo general',
        ]);

        $this->expectException(ValidationException::class);

        app(BookingEngine::class)->createAppointment($business, [
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => CarbonImmutable::parse('2026-09-07 11:00', $business->timezone),
            'status' => 'scheduled',
        ]);
    }

    public function test_conversation_manager_stores_whatsapp_contact_messages_and_state(): void
    {
        [, $business] = $this->tenantUser();
        $manager = app(ConversationManager::class);

        $contact = $manager->contactForWhatsapp($business, '573001112233', 'Ana WhatsApp', 'wa-contact-1');
        $conversation = $manager->openConversation($business, $contact);
        $message = $manager->recordIncoming($conversation, [
            'external_message_id' => 'wamid.demo-1',
            'body' => 'Hola, quiero agendar fisioterapia.',
            'payload' => ['type' => 'text'],
            'received_at' => now(),
        ]);
        $duplicate = $manager->recordIncoming($conversation, [
            'external_message_id' => 'wamid.demo-1',
            'body' => 'Mensaje duplicado por reintento.',
        ]);
        $reply = $manager->recordOutgoing($conversation, 'Claro. Que servicio deseas reservar?');
        $state = $manager->updateState($conversation, 'collecting_service', [
            'raw_service' => 'fisioterapia',
        ]);

        $this->assertSame($message->id, $duplicate->id);
        $this->assertSame('queued', $reply->status);
        $this->assertSame('collecting_service', $state->state);
        $this->assertSame(2, $conversation->messages()->count());
        $this->assertDatabaseHas('whatsapp_contacts', [
            'business_id' => $business->id,
            'phone' => '573001112233',
            'external_contact_id' => 'wa-contact-1',
        ]);
        $this->assertDatabaseHas('conversation_states', [
            'business_id' => $business->id,
            'conversation_id' => $conversation->id,
            'state' => 'collecting_service',
        ]);
    }

    public function test_whatsapp_contacts_are_isolated_by_business(): void
    {
        [, $business] = $this->tenantUser();
        [, $otherBusiness] = $this->tenantUser('Otra Clinica', 'otra@example.com');
        $manager = app(ConversationManager::class);

        $contact = $manager->contactForWhatsapp($business, '573001112233', 'Cliente propio');
        $otherContact = $manager->contactForWhatsapp($otherBusiness, '573001112233', 'Cliente ajeno');

        $this->assertNotSame($contact->id, $otherContact->id);
        $this->assertSame($business->id, $contact->business_id);
        $this->assertSame($otherBusiness->id, $otherContact->business_id);
    }

    public function test_whatsapp_simulator_can_complete_a_booking_flow(): void
    {
        [$user, $business] = $this->tenantUser();
        $business->settings()->firstOrCreate([])->update([
            'slot_interval_minutes' => 30,
            'booking_notice_minutes' => 0,
        ]);
        $service = $business->services()->create(['name' => 'Fisioterapia', 'duration_minutes' => 60, 'price_cents' => 9000000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Laura Mora', 'is_active' => true]);
        $service->professionals()->syncWithPivotValues([$professional->id], ['business_id' => $business->id]);
        $this->openWeekday($business, 1);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->get(route('whatsapp-simulator.index'))
            ->assertOk()
            ->assertSee('WhatsApp demo');

        foreach (['quiero agendar', 'Fisioterapia', 'Laura Mora', '2026-09-07', '09:00'] as $body) {
            $this->actingAs($user)
                ->withSession(['business_id' => $business->id])
                ->post(route('whatsapp-simulator.store'), [
                    'phone' => '573001112233',
                    'name' => 'Ana WhatsApp',
                    'body' => $body,
                ])
                ->assertRedirect();
        }

        $this->assertDatabaseHas('appointments', [
            'business_id' => $business->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'source_channel' => Appointment::SOURCE_WHATSAPP,
            'status' => 'scheduled',
        ]);
        $this->assertDatabaseHas('conversation_states', [
            'business_id' => $business->id,
            'state' => 'completed',
        ]);
    }

    public function test_whatsapp_simulator_understands_flexible_full_booking_message(): void
    {
        [$user, $business] = $this->tenantUser();
        $business->settings()->firstOrCreate([])->update([
            'slot_interval_minutes' => 30,
            'booking_notice_minutes' => 0,
            'whatsapp_settings' => [
                'enabled' => true,
                'phone' => '573001112233',
                'confirmation_message' => 'Tu cita quedo confirmada.',
                'appointment_status' => 'confirmed',
            ],
        ]);
        $service = $business->services()->create(['name' => 'Fisioterapia', 'duration_minutes' => 60, 'price_cents' => 9000000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Laura Mora', 'is_active' => true]);
        $service->professionals()->syncWithPivotValues([$professional->id], ['business_id' => $business->id]);
        $this->openWeekday($business, 1);

        $this->actingAs($user)
            ->withSession(['business_id' => $business->id])
            ->post(route('whatsapp-simulator.store'), [
                'phone' => '573001112233',
                'name' => 'Ana WhatsApp',
                'body' => 'Quiero agendar fisioterapia con Laura el 2026/09/07 a las 9',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'business_id' => $business->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => '2026-09-07 09:00:00',
            'source_channel' => Appointment::SOURCE_WHATSAPP,
            'status' => 'confirmed',
        ]);
    }

    public function test_whatsapp_simulator_uses_next_available_slot_after_time_preference(): void
    {
        [$user, $business] = $this->tenantUser();
        $business->settings()->firstOrCreate([])->update([
            'slot_interval_minutes' => 30,
            'booking_notice_minutes' => 0,
        ]);
        $service = $business->services()->create(['name' => 'Fisioterapia', 'duration_minutes' => 60, 'price_cents' => 9000000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Laura Mora', 'is_active' => true]);
        $service->professionals()->syncWithPivotValues([$professional->id], ['business_id' => $business->id]);
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
            ->post(route('whatsapp-simulator.store'), [
                'phone' => '573001112233',
                'name' => 'Ana WhatsApp',
                'body' => 'Quiero una cita de fisioterapia con Laura el 2026-09-07 despues de las 9',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'business_id' => $business->id,
            'service_id' => $service->id,
            'professional_id' => $professional->id,
            'starts_at' => '2026-09-07 10:00:00',
            'source_channel' => Appointment::SOURCE_WHATSAPP,
        ]);
    }

    public function test_meta_whatsapp_webhook_can_be_verified(): void
    {
        config(['services.whatsapp.verify_token' => 'trebbia-test-token']);

        $this->get('/webhooks/meta/whatsapp?hub_mode=subscribe&hub_verify_token=trebbia-test-token&hub_challenge=abc123')
            ->assertOk()
            ->assertSee('abc123');
    }

    public function test_meta_whatsapp_webhook_receives_message_and_sends_reply(): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response([
                'messages' => [['id' => 'wamid.outbound-1']],
            ], 200),
        ]);

        [, $business] = $this->tenantUser();
        $business->settings()->firstOrCreate([])->update([
            'slot_interval_minutes' => 30,
            'booking_notice_minutes' => 0,
            'whatsapp_settings' => [
                'enabled' => true,
                'phone' => '573113302090',
                'confirmation_message' => 'Tu cita quedo confirmada.',
                'appointment_status' => 'confirmed',
                'mode' => 'cloud_api',
            ],
        ]);
        $business->whatsappAccounts()->create([
            'display_name' => 'TREBBIA Salud',
            'phone' => '573113302090',
            'phone_number_id' => '1234567890',
            'waba_id' => '9876543210',
            'access_token' => 'EAAB_demo_token',
            'is_active' => true,
            'status' => 'configured',
        ]);
        $service = $business->services()->create(['name' => 'Fisioterapia', 'duration_minutes' => 60, 'price_cents' => 9000000, 'is_active' => true]);
        $professional = $business->professionals()->create(['name' => 'Laura Mora', 'is_active' => true]);
        $service->professionals()->syncWithPivotValues([$professional->id], ['business_id' => $business->id]);
        $this->openWeekday($business, 1);

        $payload = [
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'metadata' => ['phone_number_id' => '1234567890'],
                        'contacts' => [[
                            'wa_id' => '573001112233',
                            'profile' => ['name' => 'Ana WhatsApp'],
                        ]],
                        'messages' => [[
                            'from' => '573001112233',
                            'id' => 'wamid.inbound-1',
                            'timestamp' => (string) now()->timestamp,
                            'type' => 'text',
                            'text' => ['body' => 'Quiero agendar fisioterapia con Laura el 2026/09/07 a las 9'],
                        ]],
                    ],
                ]],
            ]],
        ];

        $this->postJson(route('webhooks.meta.whatsapp.receive'), $payload)
            ->assertOk()
            ->assertSee('EVENT_RECEIVED');

        $this->assertDatabaseHas('appointments', [
            'business_id' => $business->id,
            'starts_at' => '2026-09-07 09:00:00',
            'source_channel' => Appointment::SOURCE_WHATSAPP,
            'status' => 'confirmed',
        ]);
        $this->assertDatabaseHas('conversation_messages', [
            'business_id' => $business->id,
            'external_message_id' => 'wamid.inbound-1',
            'direction' => 'inbound',
        ]);
        $this->assertDatabaseHas('conversation_messages', [
            'business_id' => $business->id,
            'direction' => 'outbound',
            'status' => 'sent',
        ]);

        Http::assertSent(fn ($request) => str_contains($request->url(), '/1234567890/messages')
            && $request['to'] === '573001112233');
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
