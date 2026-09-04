@extends('layouts.app')

@section('title', 'WhatsApp automatico | TREBBIA')
@section('eyebrow', $business->name)
@section('page-title', 'WhatsApp automatico')

@section('content')
    <div class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        <main class="space-y-6">
            <section class="trebbia-card overflow-hidden">
                <div class="border-b border-[#e7ebe7] p-6">
                    <p class="text-sm font-bold uppercase tracking-[0.18em] text-[#64716d]">Activacion administrada</p>
                    <h2 class="mt-1 text-2xl font-bold">Solicita WhatsApp automatico</h2>
                    <p class="mt-2 max-w-3xl text-sm text-[#64716d]">Completa estos datos y TREBBIA se encarga de revisar, preparar y acompanar la conexion tecnica del canal. Tu equipo solo debe validar el numero cuando sea necesario.</p>
                </div>

                @if ($activationRequest)
                    <div class="grid gap-4 border-b border-[#e7ebe7] bg-[#f8faf8] p-6 md:grid-cols-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#64716d]">Estado actual</p>
                            <p class="mt-1 text-lg font-bold text-[#245f57]">{{ $statusLabels[$activationRequest->status] ?? $activationRequest->status }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#64716d]">Numero a conectar</p>
                            <p class="mt-1 font-bold">{{ $activationRequest->whatsapp_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-[#64716d]">Solicitud</p>
                            <p class="mt-1 font-bold">{{ $activationRequest->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('whatsapp-activation.store') }}" class="grid gap-6 p-6">
                    @csrf

                    <section>
                        <h3 class="text-lg font-bold">Datos del negocio</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="trebbia-label" for="commercial_name">Nombre comercial</label>
                                <input class="trebbia-input" id="commercial_name" name="commercial_name" value="{{ old('commercial_name', $business->name) }}" required>
                            </div>
                            <div>
                                <label class="trebbia-label" for="legal_name">Razon social</label>
                                <input class="trebbia-input" id="legal_name" name="legal_name" value="{{ old('legal_name', $activationRequest?->legal_name) }}">
                            </div>
                            <div>
                                <label class="trebbia-label" for="tax_id">NIT o identificacion fiscal</label>
                                <input class="trebbia-input" id="tax_id" name="tax_id" value="{{ old('tax_id', $activationRequest?->tax_id) }}">
                            </div>
                            <div>
                                <label class="trebbia-label" for="industry">Actividad del negocio</label>
                                <input class="trebbia-input" id="industry" name="industry" value="{{ old('industry', $business->industry) }}">
                            </div>
                            <div>
                                <label class="trebbia-label" for="country">Pais</label>
                                <input class="trebbia-input" id="country" name="country" value="{{ old('country', 'Colombia') }}" required>
                            </div>
                            <div>
                                <label class="trebbia-label" for="city">Ciudad</label>
                                <input class="trebbia-input" id="city" name="city" value="{{ old('city', $activationRequest?->city) }}">
                            </div>
                            <div class="md:col-span-2">
                                <label class="trebbia-label" for="address">Direccion</label>
                                <input class="trebbia-input" id="address" name="address" value="{{ old('address', $activationRequest?->address) }}">
                            </div>
                            <div>
                                <label class="trebbia-label" for="website_or_instagram">Sitio web o Instagram</label>
                                <input class="trebbia-input" id="website_or_instagram" name="website_or_instagram" value="{{ old('website_or_instagram', $activationRequest?->website_or_instagram) }}" placeholder="https://... o @usuario">
                            </div>
                            <div>
                                <label class="trebbia-label" for="public_email">Correo publico</label>
                                <input class="trebbia-input" id="public_email" type="email" name="public_email" value="{{ old('public_email', $business->email) }}">
                            </div>
                            <div>
                                <label class="trebbia-label" for="public_phone">Telefono publico</label>
                                <input class="trebbia-input" id="public_phone" name="public_phone" value="{{ old('public_phone', $business->phone) }}">
                            </div>
                        </div>
                    </section>

                    <section class="border-t border-[#e7ebe7] pt-6">
                        <h3 class="text-lg font-bold">Responsable de activacion</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="trebbia-label" for="responsible_name">Nombre del responsable</label>
                                <input class="trebbia-input" id="responsible_name" name="responsible_name" value="{{ old('responsible_name', $activationRequest?->responsible_name) }}" required>
                            </div>
                            <div>
                                <label class="trebbia-label" for="responsible_role">Cargo</label>
                                <input class="trebbia-input" id="responsible_role" name="responsible_role" value="{{ old('responsible_role', $activationRequest?->responsible_role) }}">
                            </div>
                            <div>
                                <label class="trebbia-label" for="responsible_email">Correo del responsable</label>
                                <input class="trebbia-input" id="responsible_email" type="email" name="responsible_email" value="{{ old('responsible_email', $business->email) }}" required>
                            </div>
                            <div>
                                <label class="trebbia-label" for="responsible_phone">Celular del responsable</label>
                                <input class="trebbia-input" id="responsible_phone" name="responsible_phone" value="{{ old('responsible_phone', $business->phone) }}" required>
                            </div>
                        </div>
                    </section>

                    <section class="border-t border-[#e7ebe7] pt-6">
                        <h3 class="text-lg font-bold">Numero y perfil de WhatsApp</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="trebbia-label" for="whatsapp_number">Numero WhatsApp a conectar</label>
                                <input class="trebbia-input" id="whatsapp_number" name="whatsapp_number" value="{{ old('whatsapp_number', $activationRequest?->whatsapp_number ?? $business->phone) }}" placeholder="+57 311 330 2090" required>
                            </div>
                            <div>
                                <label class="trebbia-label" for="verification_method">Puede recibir verificacion por</label>
                                <select class="trebbia-input" id="verification_method" name="verification_method" required>
                                    <option value="sms" @selected(old('verification_method', $activationRequest?->verification_method) === 'sms')>SMS</option>
                                    <option value="call" @selected(old('verification_method', $activationRequest?->verification_method) === 'call')>Llamada</option>
                                    <option value="both" @selected(old('verification_method', $activationRequest?->verification_method) === 'both')>SMS o llamada</option>
                                </select>
                            </div>
                            <label class="flex items-center gap-2 rounded-md border border-[#d7ddd7] bg-white p-4 text-sm font-semibold text-[#53615d] md:col-span-2">
                                <input type="hidden" name="uses_whatsapp_business" value="0">
                                <input type="checkbox" name="uses_whatsapp_business" value="1" @checked(old('uses_whatsapp_business', $activationRequest?->uses_whatsapp_business))>
                                El numero ya usa WhatsApp Business
                            </label>
                            <div>
                                <label class="trebbia-label" for="whatsapp_display_name">Nombre visible en WhatsApp</label>
                                <input class="trebbia-input" id="whatsapp_display_name" name="whatsapp_display_name" value="{{ old('whatsapp_display_name', $activationRequest?->whatsapp_display_name ?? $business->name) }}" required>
                            </div>
                            <div>
                                <label class="trebbia-label" for="whatsapp_category">Categoria</label>
                                <select class="trebbia-input" id="whatsapp_category" name="whatsapp_category">
                                    <option value="">Seleccionar</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category }}" @selected(old('whatsapp_category', $activationRequest?->whatsapp_category) === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="trebbia-label" for="whatsapp_description">Descripcion corta</label>
                                <textarea class="trebbia-input min-h-24" id="whatsapp_description" name="whatsapp_description">{{ old('whatsapp_description', $activationRequest?->whatsapp_description) }}</textarea>
                            </div>
                            <div class="md:col-span-2">
                                <label class="trebbia-label" for="business_hours">Horario de atencion</label>
                                <textarea class="trebbia-input min-h-24" id="business_hours" name="business_hours" placeholder="Lunes a viernes de 8:00 a 18:00">{{ old('business_hours', $activationRequest?->business_hours) }}</textarea>
                            </div>
                        </div>
                    </section>

                    <section class="border-t border-[#e7ebe7] pt-6">
                        <h3 class="text-lg font-bold">Autorizaciones</h3>
                        <div class="mt-4 grid gap-3">
                            <label class="flex items-start gap-2 rounded-md border border-[#d7ddd7] bg-white p-4 text-sm font-semibold text-[#53615d]">
                                <input class="mt-1" type="checkbox" name="number_owner_confirmed" value="1" required @checked(old('number_owner_confirmed'))>
                                <span>Confirmo que el numero pertenece al negocio o esta autorizado para ser usado por el negocio.</span>
                            </label>
                            <label class="flex items-start gap-2 rounded-md border border-[#d7ddd7] bg-white p-4 text-sm font-semibold text-[#53615d]">
                                <input class="mt-1" type="checkbox" name="managed_activation_accepted" value="1" required @checked(old('managed_activation_accepted'))>
                                <span>Acepto que TREBBIA gestione la activacion tecnica del canal WhatsApp automatico.</span>
                            </label>
                            <label class="flex items-start gap-2 rounded-md border border-[#d7ddd7] bg-white p-4 text-sm font-semibold text-[#53615d]">
                                <input class="mt-1" type="checkbox" name="messaging_costs_accepted" value="1" required @checked(old('messaging_costs_accepted'))>
                                <span>Entiendo que WhatsApp automatico puede estar sujeto a costos incluidos o adicionales segun el plan contratado con TREBBIA.</span>
                            </label>
                        </div>
                    </section>

                    <section class="border-t border-[#e7ebe7] pt-6">
                        <label class="trebbia-label" for="client_notes">Notas adicionales</label>
                        <textarea class="trebbia-input min-h-24" id="client_notes" name="client_notes" placeholder="Cuéntanos si el numero ya recibe muchas conversaciones, si hay varias sedes o si necesitas algun flujo especial.">{{ old('client_notes', $activationRequest?->client_notes) }}</textarea>
                    </section>

                    <div class="flex flex-col gap-3 border-t border-[#e7ebe7] pt-6 sm:flex-row">
                        <button class="trebbia-button">Enviar solicitud</button>
                        <a class="trebbia-button trebbia-button-secondary" href="{{ route('sharing.index') }}">Volver a compartir reservas</a>
                    </div>
                </form>
            </section>
        </main>

        <aside class="space-y-6">
            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Como funciona</h2>
                <div class="mt-4 space-y-4 text-sm text-[#53615d]">
                    <p><span class="font-bold text-[#18211f]">1.</span> Envias la solicitud con datos del negocio.</p>
                    <p><span class="font-bold text-[#18211f]">2.</span> TREBBIA revisa el numero y el perfil de WhatsApp.</p>
                    <p><span class="font-bold text-[#18211f]">3.</span> Coordinamos contigo la validacion por SMS o llamada.</p>
                    <p><span class="font-bold text-[#18211f]">4.</span> Activamos respuestas automaticas y pruebas reales.</p>
                </div>
            </section>

            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Estados</h2>
                <div class="mt-4 space-y-2 text-sm text-[#53615d]">
                    @foreach ($statusLabels as $status)
                        <p class="rounded-md border border-[#e1e6e0] bg-white px-3 py-2">{{ $status }}</p>
                    @endforeach
                </div>
            </section>

            <section class="trebbia-card p-5">
                <h2 class="text-lg font-bold">Importante</h2>
                <p class="mt-3 text-sm text-[#64716d]">El cliente no necesita crear Meta Developers ni copiar tokens. TREBBIA administra esa parte y solo solicita la validacion del numero cuando haga falta.</p>
            </section>
        </aside>
    </div>
@endsection
