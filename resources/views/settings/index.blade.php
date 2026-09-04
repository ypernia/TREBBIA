@extends('layouts.app')

@section('title', 'Configuracion | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'Configuracion')

@section('content')
    @php
        $publicBooking = $settings->public_booking_settings ?? [];
        $notifications = $settings->notification_preferences ?? [];
        $whatsapp = array_merge($whatsappDefaults, $settings->whatsapp_settings ?? []);
        $whatsappPhone = preg_replace('/\D+/', '', $whatsapp['phone'] ?? '');
        $whatsappLink = $whatsappPhone ? 'https://wa.me/'.$whatsappPhone.'?text='.rawurlencode($whatsapp['entry_message'] ?? '') : null;
        $whatsappQr = $whatsappLink ? 'https://api.qrserver.com/v1/create-qr-code/?size=220x220&data='.rawurlencode($whatsappLink) : null;
        $webhookUrl = route('webhooks.meta.whatsapp.receive');
        $whatsappModeLabel = match ($whatsapp['mode'] ?? 'simulated') {
            'cloud_api_manual' => 'Meta Cloud API manual',
            'simulated' => 'Modo enlace',
            default => 'Modo enlace',
        };
        $settingsTabs = [
            ['key' => 'profile', 'label' => 'Perfil', 'icon' => 'briefcase'],
            ['key' => 'agenda', 'label' => 'Agenda', 'icon' => 'calendar'],
            ['key' => 'whatsapp', 'label' => 'WhatsApp', 'icon' => 'message'],
            ['key' => 'branches', 'label' => 'Sedes', 'icon' => 'box'],
            ['key' => 'team', 'label' => 'Equipo', 'icon' => 'users'],
        ];
    @endphp

    <div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <p class="max-w-2xl text-sm text-[#64716d]">Configura solo lo necesario para operar: negocio, agenda, WhatsApp, sedes y equipo.</p>
        <a class="trebbia-button trebbia-button-secondary" href="{{ route('schedules.edit') }}">Editar horarios generales</a>
    </div>

    @include('partials.errors')

    <div class="trebbia-card mb-5 overflow-x-auto p-2" role="tablist" aria-label="Secciones de configuracion">
        <div class="flex min-w-max gap-1">
            @foreach ($settingsTabs as $tab)
                <button class="trebbia-tab" type="button" role="tab" aria-selected="{{ $loop->first ? 'true' : 'false' }}" data-settings-tab="{{ $tab['key'] }}">
                    <x-icon :name="$tab['icon']" class="size-4" />
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <main class="space-y-6">
            <section class="trebbia-card p-6" data-settings-panel="profile">
                <h2 class="text-xl font-bold">Perfil del negocio</h2>
                <form method="POST" action="{{ route('settings.business.update') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div class="sm:col-span-2">
                        <label class="trebbia-label" for="name">Nombre</label>
                        <input class="trebbia-input" id="name" name="name" value="{{ old('name', $business->name) }}" required>
                    </div>
                    <div>
                        <label class="trebbia-label" for="industry">Industria</label>
                        <input class="trebbia-input" id="industry" name="industry" value="{{ old('industry', $business->industry) }}">
                    </div>
                    <div>
                        <label class="trebbia-label" for="email">Correo</label>
                        <input class="trebbia-input" id="email" type="email" name="email" value="{{ old('email', $business->email) }}">
                    </div>
                    <div>
                        <label class="trebbia-label" for="phone">Telefono</label>
                        <input class="trebbia-input" id="phone" name="phone" value="{{ old('phone', $business->phone) }}">
                    </div>
                    <div>
                        <label class="trebbia-label" for="timezone">Zona horaria</label>
                        <select class="trebbia-input" id="timezone" name="timezone" required>
                            @foreach ($timezones as $value => $label)
                                <option value="{{ $value }}" @selected(old('timezone', $business->timezone) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="trebbia-label" for="currency">Moneda</label>
                        <select class="trebbia-input" id="currency" name="currency" required>
                            @foreach ($currencies as $value => $label)
                                <option value="{{ $value }}" @selected(old('currency', $business->currency) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2">
                        <button class="trebbia-button">Guardar perfil</button>
                    </div>
                </form>
            </section>

            <section id="agenda-preferences" class="trebbia-card p-6" data-settings-panel="agenda">
                <h2 class="text-xl font-bold">Preferencias de agenda</h2>
                <form method="POST" action="{{ route('settings.preferences.update') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="trebbia-label" for="slot_interval_minutes">Intervalo de agenda</label>
                        <select class="trebbia-input" id="slot_interval_minutes" name="slot_interval_minutes" required>
                            @foreach ($slotIntervals as $minutes)
                                <option value="{{ $minutes }}" @selected((int) old('slot_interval_minutes', $settings->slot_interval_minutes) === $minutes)>{{ $minutes }} minutos</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="trebbia-label" for="booking_notice_minutes">Aviso minimo para reservar</label>
                        <select class="trebbia-input" id="booking_notice_minutes" name="booking_notice_minutes" required>
                            @foreach ($bookingNoticeOptions as $minutes => $label)
                                <option value="{{ $minutes }}" @selected((int) old('booking_notice_minutes', $settings->booking_notice_minutes) === $minutes)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <label class="flex items-center gap-2 rounded-md border border-[#d7ddd7] bg-white p-4 text-sm font-semibold text-[#53615d]">
                        <input type="hidden" name="allow_public_booking" value="0">
                        <input type="checkbox" name="allow_public_booking" value="1" @checked(old('allow_public_booking', $publicBooking['allow_public_booking'] ?? false))>
                        Permitir reservas publicas
                    </label>
                    <label class="flex items-center gap-2 rounded-md border border-[#d7ddd7] bg-white p-4 text-sm font-semibold text-[#53615d]">
                        <input type="hidden" name="require_manual_confirmation" value="0">
                        <input type="checkbox" name="require_manual_confirmation" value="1" @checked(old('require_manual_confirmation', $publicBooking['require_manual_confirmation'] ?? true))>
                        Requerir confirmacion manual
                    </label>
                    <label class="flex items-center gap-2 rounded-md border border-[#d7ddd7] bg-white p-4 text-sm font-semibold text-[#53615d]">
                        <input type="hidden" name="notify_email" value="0">
                        <input type="checkbox" name="notify_email" value="1" @checked(old('notify_email', $notifications['email'] ?? false))>
                        Notificaciones por email
                    </label>
                    <label class="flex items-center gap-2 rounded-md border border-[#d7ddd7] bg-white p-4 text-sm font-semibold text-[#53615d]">
                        <input type="hidden" name="notify_whatsapp" value="0">
                        <input type="checkbox" name="notify_whatsapp" value="1" @checked(old('notify_whatsapp', $notifications['whatsapp'] ?? false))>
                        Notificaciones por WhatsApp
                    </label>
                    <div class="sm:col-span-2">
                        <button class="trebbia-button">Guardar preferencias</button>
                    </div>
                </form>
            </section>

            <section class="trebbia-card overflow-hidden" data-settings-panel="whatsapp">
                <div class="border-b border-[#e7ebe7] p-6">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#64716d]">Canal comercial WhatsApp</p>
                            <h2 class="mt-1 text-2xl font-bold">Reservas por enlace y QR</h2>
                            <p class="mt-2 max-w-2xl text-sm text-[#64716d]">Activa una entrada sencilla para que el cliente abra WhatsApp, escriba al negocio y reserve mientras TREBBIA mantiene la agenda ordenada.</p>
                        </div>
                        <div class="rounded-md border border-[#d7ddd7] bg-[#f8faf8] px-4 py-3">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#64716d]">Estado</p>
                            <p class="mt-1 text-lg font-bold text-[#245f57]">{{ $whatsappSetup['status'] }}</p>
                            <p class="mt-1 text-sm text-[#53615d]">{{ $whatsappSetup['completed'] }} de {{ $whatsappSetup['total'] }} pasos</p>
                        </div>
                    </div>
                    <div class="mt-5 h-2 overflow-hidden rounded-full bg-[#edf2ef]">
                        <div class="h-full rounded-full bg-[#245f57]" style="width: {{ $whatsappSetup['percent'] }}%"></div>
                    </div>
                </div>

                <div class="grid gap-4 p-6 lg:grid-cols-5">
                    @foreach ($whatsappSetup['steps'] as $step)
                        <div class="rounded-md border {{ $step['complete'] ? 'border-[#cfe4da] bg-[#f3faf7]' : 'border-[#e1e6e0] bg-white' }} p-4">
                            <span class="inline-flex rounded-md px-2 py-1 text-xs font-bold {{ $step['complete'] ? 'bg-[#dff2eb] text-[#245f57]' : 'bg-[#f1f1ef] text-[#53615d]' }}">
                                {{ $step['complete'] ? 'Listo' : 'Pendiente' }}
                            </span>
                            <h3 class="mt-3 font-bold">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm text-[#64716d]">{{ $step['description'] }}</p>
                            @if ($step['action'])
                                <a class="mt-4 inline-flex text-sm font-bold text-[#245f57] hover:underline" href="{{ $step['action'] }}" @if (str_starts_with($step['action'], 'http')) target="_blank" @endif>
                                    {{ $step['action_label'] }}
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>

                <div class="grid gap-4 border-t border-[#e7ebe7] bg-[#fbfcfb] p-6 lg:grid-cols-[1.2fr_1fr]">
                    <div class="rounded-md border border-[#d7ddd7] bg-white p-5">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Modo enlace</p>
                        <h3 class="mt-2 text-lg font-bold">Listo para vender sin Meta Developers</h3>
                        <p class="mt-2 text-sm text-[#64716d]">El cliente toca el boton o escanea el QR, abre WhatsApp con un mensaje preparado y el negocio confirma la cita apoyandose en la agenda de TREBBIA.</p>
                        <div class="mt-4 flex flex-col gap-3 sm:flex-row">
                            @if ($whatsappLink)
                                <a class="trebbia-button" href="{{ $whatsappLink }}" target="_blank">Probar WhatsApp</a>
                            @endif
                            <a class="trebbia-button trebbia-button-secondary" href="{{ route('public-booking.show', $business->slug) }}" target="_blank">Ver pagina publica</a>
                        </div>
                    </div>
                    <div id="assisted-activation" class="rounded-md border border-[#e1e6e0] bg-white p-5">
                        <p class="text-sm font-bold uppercase tracking-[0.16em] text-[#64716d]">Modo automatico</p>
                        <h3 class="mt-2 text-lg font-bold">Activacion asistida</h3>
                        <p class="mt-2 text-sm text-[#64716d]">Para respuestas automaticas reales, TREBBIA acompana la configuracion de Meta Cloud API con la cuenta, numero y facturacion del negocio.</p>
                        <p class="mt-4 rounded-md bg-[#f1f4f1] px-3 py-2 text-sm font-bold text-[#53615d]">Estado: {{ $whatsappSetup['has_cloud_api'] ? 'Credenciales guardadas' : 'No iniciado' }}</p>
                    </div>
                </div>
            </section>

            <section id="whatsapp-channel" class="trebbia-card p-6" data-settings-panel="whatsapp">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h2 class="text-xl font-bold">Canal WhatsApp</h2>
                        <p class="mt-1 text-sm text-[#64716d]">Configura el numero, el mensaje inicial, el enlace y el QR que vera el cliente. Modo actual: {{ $whatsappModeLabel }}.</p>
                    </div>
                    <span class="w-fit rounded-md px-3 py-2 text-xs font-bold {{ ($whatsapp['enabled'] ?? false) ? 'bg-[#edf7f4] text-[#245f57]' : 'bg-[#f1f1ef] text-[#53615d]' }}">
                        {{ ($whatsapp['enabled'] ?? false) ? 'Activo' : 'Inactivo' }}
                    </span>
                </div>

                <form method="POST" action="{{ route('settings.whatsapp.update') }}" class="mt-5 grid gap-4 sm:grid-cols-2">
                    @csrf
                    @method('PUT')
                    <label class="flex items-center gap-2 rounded-md border border-[#d7ddd7] bg-white p-4 text-sm font-semibold text-[#53615d] sm:col-span-2">
                        <input type="hidden" name="enabled" value="0">
                        <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $whatsapp['enabled'] ?? false))>
                        Activar reservas por WhatsApp
                    </label>
                    <div>
                        <label class="trebbia-label" for="whatsapp_phone">Numero WhatsApp del negocio</label>
                        <input class="trebbia-input" id="whatsapp_phone" name="phone" value="{{ old('phone', $whatsapp['phone'] ?? '') }}" placeholder="573113302090">
                    </div>
                    <div>
                        <label class="trebbia-label" for="whatsapp_display_name">Nombre visible</label>
                        <input class="trebbia-input" id="whatsapp_display_name" name="display_name" value="{{ old('display_name', $whatsapp['display_name'] ?? $business->name) }}">
                    </div>
                    <div>
                        <label class="trebbia-label" for="appointment_status">Estado de cita creada por WhatsApp</label>
                        <select class="trebbia-input" id="appointment_status" name="appointment_status" required>
                            @foreach ($appointmentStatusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(old('appointment_status', $whatsapp['appointment_status'] ?? 'scheduled') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="trebbia-label" for="entry_message">Mensaje inicial del enlace</label>
                        <input class="trebbia-input" id="entry_message" name="entry_message" value="{{ old('entry_message', $whatsapp['entry_message'] ?? '') }}" required>
                    </div>
                    <details class="sm:col-span-2 rounded-md border border-[#e1e6e0] bg-[#fbfcfb] p-4">
                        <summary class="cursor-pointer text-sm font-bold text-[#245f57]">Activacion automatica asistida con Meta Cloud API</summary>
                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                            <div class="sm:col-span-2 rounded-md border border-[#bcd7df] bg-[#f1fbfd] p-4 text-sm text-[#33535b]">
                                <p class="font-bold text-[#12343b]">Solo para planes con acompanamiento</p>
                                <p class="mt-1">Estos datos se completan cuando TREBBIA acompane al negocio en Meta. La WABA, el numero y la facturacion deben pertenecer al negocio.</p>
                            </div>
                            <div>
                                <label class="trebbia-label" for="phone_number_id">Meta phone number ID</label>
                                <input class="trebbia-input" id="phone_number_id" name="phone_number_id" value="{{ old('phone_number_id', $whatsappAccount?->phone_number_id) }}" placeholder="Ej: 123456789012345">
                            </div>
                            <div>
                                <label class="trebbia-label" for="waba_id">WhatsApp Business Account ID</label>
                                <input class="trebbia-input" id="waba_id" name="waba_id" value="{{ old('waba_id', $whatsappAccount?->waba_id) }}" placeholder="Ej: 987654321098765">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="trebbia-label" for="access_token">Token de acceso Cloud API</label>
                                <input class="trebbia-input" id="access_token" name="access_token" type="password" autocomplete="new-password" placeholder="{{ $whatsappAccount?->access_token ? 'Token guardado cifrado. Escribe uno nuevo solo si deseas reemplazarlo.' : 'Se completa durante la activacion asistida' }}">
                            </div>
                            <label class="flex items-start gap-2 rounded-md border border-[#d7ddd7] bg-white p-4 text-sm font-semibold text-[#53615d] sm:col-span-2">
                                <input type="hidden" name="billing_owner_confirmed" value="0">
                                <input class="mt-1" type="checkbox" name="billing_owner_confirmed" value="1" @checked(old('billing_owner_confirmed', $whatsapp['billing_owner_confirmed'] ?? false))>
                                <span>Confirmo que la WABA, el numero de WhatsApp Business y la configuracion de facturacion pertenecen a este negocio.</span>
                            </label>
                        </div>
                    </details>
                    <div class="sm:col-span-2">
                        <label class="trebbia-label" for="welcome_message">Mensaje de bienvenida</label>
                        <textarea class="trebbia-input min-h-24" id="welcome_message" name="welcome_message" required>{{ old('welcome_message', $whatsapp['welcome_message'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="trebbia-label" for="unavailable_message">Mensaje sin disponibilidad</label>
                        <textarea class="trebbia-input min-h-24" id="unavailable_message" name="unavailable_message" required>{{ old('unavailable_message', $whatsapp['unavailable_message'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="trebbia-label" for="confirmation_message">Mensaje de confirmacion</label>
                        <textarea class="trebbia-input min-h-24" id="confirmation_message" name="confirmation_message" required>{{ old('confirmation_message', $whatsapp['confirmation_message'] ?? '') }}</textarea>
                    </div>
                    <div class="sm:col-span-2 flex flex-col gap-3 sm:flex-row">
                        <button class="trebbia-button">Guardar WhatsApp</button>
                        <a class="trebbia-button trebbia-button-secondary" href="{{ route('whatsapp-simulator.index') }}">Abrir simulador</a>
                        @if ($whatsappLink)
                            <a class="trebbia-button trebbia-button-secondary" href="{{ $whatsappLink }}" target="_blank">Probar enlace</a>
                        @endif
                    </div>
                </form>
            </section>

            <section id="branches" class="trebbia-card overflow-hidden" data-settings-panel="branches">
                <div class="border-b border-[#e7ebe7] p-6">
                    <h2 class="text-xl font-bold">Sedes</h2>
                    <p class="mt-1 text-sm text-[#64716d]">Configura sucursales, consultorios o puntos de atencion.</p>
                </div>

                @foreach ($branches as $branch)
                    <form method="POST" action="{{ route('settings.branches.update', $branch) }}" class="grid gap-3 border-b border-[#e7ebe7] p-5 lg:grid-cols-[1fr_10rem_1fr_7rem_7rem_auto] lg:items-end">
                        @csrf
                        @method('PUT')
                        <div>
                            <label class="trebbia-label" for="branch_{{ $branch->id }}_name">Nombre</label>
                            <input class="trebbia-input" id="branch_{{ $branch->id }}_name" name="name" value="{{ $branch->name }}" required>
                        </div>
                        <div>
                            <label class="trebbia-label" for="branch_{{ $branch->id }}_phone">Telefono</label>
                            <input class="trebbia-input" id="branch_{{ $branch->id }}_phone" name="phone" value="{{ $branch->phone }}">
                        </div>
                        <div>
                            <label class="trebbia-label" for="branch_{{ $branch->id }}_address">Direccion</label>
                            <input class="trebbia-input" id="branch_{{ $branch->id }}_address" name="address" value="{{ $branch->address }}">
                        </div>
                        <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-[#53615d]">
                            <input type="hidden" name="is_main" value="0">
                            <input type="checkbox" name="is_main" value="1" @checked($branch->is_main)>
                            Principal
                        </label>
                        <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-[#53615d]">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked($branch->is_active)>
                            Activa
                        </label>
                        <button class="trebbia-button trebbia-button-secondary">Actualizar</button>
                    </form>
                @endforeach

                <form method="POST" action="{{ route('settings.branches.store') }}" class="grid gap-3 bg-[#f8faf8] p-5 lg:grid-cols-[1fr_10rem_1fr_7rem_7rem_auto] lg:items-end">
                    @csrf
                    <div>
                        <label class="trebbia-label" for="new_branch_name">Nueva sede</label>
                        <input class="trebbia-input" id="new_branch_name" name="name" placeholder="Sede norte" required>
                    </div>
                    <div>
                        <label class="trebbia-label" for="new_branch_phone">Telefono</label>
                        <input class="trebbia-input" id="new_branch_phone" name="phone">
                    </div>
                    <div>
                        <label class="trebbia-label" for="new_branch_address">Direccion</label>
                        <input class="trebbia-input" id="new_branch_address" name="address">
                    </div>
                    <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-[#53615d]">
                        <input type="hidden" name="is_main" value="0">
                        <input type="checkbox" name="is_main" value="1">
                        Principal
                    </label>
                    <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-[#53615d]">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" checked>
                        Activa
                    </label>
                    <button class="trebbia-button">Crear sede</button>
                </form>
            </section>

            <section id="team" class="trebbia-card overflow-hidden" data-settings-panel="team">
                <div class="border-b border-[#e7ebe7] p-6">
                    <h2 class="text-xl font-bold">Equipo interno</h2>
                    <p class="mt-1 text-sm text-[#64716d]">Invita usuarios, asigna roles y vincula profesionales del negocio.</p>
                </div>

                <form method="POST" action="{{ route('team.invitations.store') }}" class="grid gap-3 border-b border-[#e7ebe7] bg-[#f8faf8] p-5 lg:grid-cols-[1fr_1fr_10rem_1fr_auto] lg:items-end">
                    @csrf
                    <div>
                        <label class="trebbia-label" for="invite_name">Nombre</label>
                        <input class="trebbia-input" id="invite_name" name="name" placeholder="Nombre del usuario">
                    </div>
                    <div>
                        <label class="trebbia-label" for="invite_email">Correo</label>
                        <input class="trebbia-input" id="invite_email" type="email" name="email" required>
                    </div>
                    <div>
                        <label class="trebbia-label" for="invite_role">Rol</label>
                        <select class="trebbia-input" id="invite_role" name="role" required>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="trebbia-label" for="invite_professional_id">Profesional</label>
                        <select class="trebbia-input" id="invite_professional_id" name="professional_id">
                            <option value="">Sin vincular</option>
                            @foreach ($professionals as $professional)
                                <option value="{{ $professional->id }}">{{ $professional->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button class="trebbia-button">Invitar</button>
                </form>

                @foreach ($users as $businessUser)
                    @php $linkedProfessional = $professionals->firstWhere('user_id', $businessUser->user_id); @endphp
                    <form method="POST" action="{{ route('team.members.update', $businessUser) }}" class="grid gap-3 border-b border-[#e7ebe7] p-5 lg:grid-cols-[1fr_10rem_1fr_7rem_auto] lg:items-end">
                        @csrf
                        @method('PATCH')
                        <div>
                            <p class="font-bold">{{ $businessUser->user->name }}</p>
                            <p class="mt-1 text-sm text-[#64716d]">{{ $businessUser->user->email }}</p>
                        </div>
                        <div>
                            <label class="trebbia-label" for="member_{{ $businessUser->id }}_role">Rol</label>
                            <select class="trebbia-input" id="member_{{ $businessUser->id }}_role" name="role" @disabled($businessUser->role === 'owner')>
                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}" @selected($businessUser->role === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            @if ($businessUser->role === 'owner')
                                <input type="hidden" name="role" value="owner">
                            @endif
                        </div>
                        <div>
                            <label class="trebbia-label" for="member_{{ $businessUser->id }}_professional_id">Profesional</label>
                            <select class="trebbia-input" id="member_{{ $businessUser->id }}_professional_id" name="professional_id">
                                <option value="">Sin vincular</option>
                                @foreach ($professionals as $professional)
                                    <option value="{{ $professional->id }}" @selected($linkedProfessional?->id === $professional->id)>{{ $professional->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <label class="flex items-center gap-2 pb-3 text-sm font-semibold text-[#53615d]">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked($businessUser->is_active) @disabled($businessUser->role === 'owner')>
                            Activo
                        </label>
                        <button class="trebbia-button trebbia-button-secondary">Actualizar</button>
                    </form>
                @endforeach

                @if ($invitations->isNotEmpty())
                    <div class="border-b border-[#e7ebe7] p-5">
                        <h3 class="font-bold">Invitaciones pendientes</h3>
                        <div class="mt-3 space-y-3">
                            @foreach ($invitations as $invitation)
                                <div class="grid gap-3 rounded-md border border-[#e1e6e0] bg-white p-4 md:grid-cols-[1fr_10rem_auto] md:items-center">
                                    <div>
                                        <p class="font-bold">{{ $invitation->name ?: $invitation->email }}</p>
                                        <p class="mt-1 text-sm text-[#64716d]">{{ $invitation->email }}</p>
                                    </div>
                                    <span class="w-fit rounded-md bg-[#fff9eb] px-2 py-1 text-xs font-bold text-[#765214]">{{ $roles[$invitation->role] ?? $invitation->role }}</span>
                                    <form method="POST" action="{{ route('team.invitations.cancel', $invitation) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="rounded-md border border-[#f0c9c4] px-3 py-2 text-sm font-bold text-[#8a3027]">Cancelar</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </main>

        <aside class="space-y-6">
            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Resumen</h2>
                <div class="mt-4 space-y-3 text-sm text-[#53615d]">
                    <p><span class="font-bold text-[#18211f]">Estado:</span> {{ $business->status }}</p>
                    <p><span class="font-bold text-[#18211f]">Zona horaria:</span> {{ $business->timezone }}</p>
                    <p><span class="font-bold text-[#18211f]">Moneda:</span> {{ $business->currency }}</p>
                    <p><span class="font-bold text-[#18211f]">Intervalo:</span> {{ $settings->slot_interval_minutes }} minutos</p>
                </div>
            </section>

            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Reserva publica</h2>
                <div class="mt-4 space-y-3 text-sm text-[#53615d]">
                    <p><span class="font-bold text-[#18211f]">Estado:</span> {{ ($publicBooking['allow_public_booking'] ?? false) ? 'Activa' : 'Inactiva' }}</p>
                    <a class="break-all font-bold text-[#245f57] hover:underline" href="{{ route('public-booking.show', $business->slug) }}" target="_blank">
                        {{ route('public-booking.show', $business->slug) }}
                    </a>
                </div>
            </section>

            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Entrada por WhatsApp</h2>
                <div class="mt-4 space-y-4 text-sm text-[#53615d]">
                    <p><span class="font-bold text-[#18211f]">Estado:</span> {{ ($whatsapp['enabled'] ?? false) ? 'Activa' : 'Inactiva' }}</p>
                    <p><span class="font-bold text-[#18211f]">Numero:</span> {{ $whatsappPhone ?: 'Sin configurar' }}</p>
                    <p><span class="font-bold text-[#18211f]">Modo:</span> {{ $whatsappModeLabel }}</p>
                    <p><span class="font-bold text-[#18211f]">Automatico:</span> {{ $whatsappAccount?->status ?: 'Disponible con activacion asistida' }}</p>
                    <p><span class="font-bold text-[#18211f]">Facturacion Meta:</span> {{ ($whatsapp['billing_model'] ?? null) === 'business_owned' ? 'Del negocio' : 'No aplica en modo enlace' }}</p>
                    @if ($whatsappLink)
                        <a class="break-all font-bold text-[#245f57] hover:underline" href="{{ $whatsappLink }}" target="_blank">{{ $whatsappLink }}</a>
                        <div class="rounded-md border border-[#e1e6e0] bg-white p-3">
                            <img class="mx-auto h-40 w-40" src="{{ $whatsappQr }}" alt="QR WhatsApp {{ $business->name }}">
                        </div>
                    @else
                        <p class="rounded-md border border-dashed border-[#cfd8d2] p-3">Agrega un numero para generar enlace y QR.</p>
                    @endif
                </div>
            </section>

            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-5">
                    <h2 class="text-lg font-bold">Usuarios y roles</h2>
                    <p class="mt-1 text-sm text-[#64716d]">Cupos activos y permisos base por rol.</p>
                </div>
                <div class="border-b border-[#e7ebe7] p-5">
                    <p class="text-sm font-bold text-[#18211f]">Cupos del plan</p>
                    <p class="mt-1 text-sm text-[#64716d]">{{ $users->where('is_active', true)->count() }} / {{ $userLimit ?: 'ilimitado' }} usuarios activos</p>
                </div>
                @foreach ($users as $businessUser)
                    <div class="border-b border-[#e7ebe7] p-5">
                        <p class="font-bold">{{ $businessUser->user->name }}</p>
                        <p class="mt-1 text-sm text-[#64716d]">{{ $businessUser->user->email }}</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="rounded-md bg-[#edf7f4] px-2 py-1 text-xs font-bold text-[#245f57]">{{ config("trebbia.roles.{$businessUser->role}", $businessUser->role) }}</span>
                            <span class="rounded-md px-2 py-1 text-xs font-bold {{ $businessUser->is_active ? 'bg-[#edf7f4] text-[#245f57]' : 'bg-[#f1f1ef] text-[#53615d]' }}">{{ $businessUser->is_active ? 'Activo' : 'Inactivo' }}</span>
                        </div>
                    </div>
                @endforeach
            </section>
        </aside>
    </div>
@endsection
