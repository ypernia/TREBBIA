<?php

namespace App\Services;

use App\Models\Business;

class BookingShareCenter
{
    public function for(Business $business): array
    {
        $settings = $business->settings()->firstOrCreate([]);
        $publicBooking = $settings->public_booking_settings ?? [];
        $whatsapp = $settings->whatsapp_settings ?? [];
        $phone = preg_replace('/\D+/', '', $whatsapp['phone'] ?? '');
        $entryMessage = $whatsapp['entry_message'] ?? 'Hola, quiero agendar una cita';
        $publicUrl = route('public-booking.show', $business->slug);
        $whatsappUrl = (($whatsapp['enabled'] ?? false) && $phone)
            ? 'https://wa.me/'.$phone.'?text='.rawurlencode($entryMessage)
            : null;

        $checklist = $this->checklist($business, $publicBooking, $whatsapp, $phone);
        $completed = collect($checklist)->where('complete', true)->count();

        return [
            'public_url' => $publicUrl,
            'whatsapp_url' => $whatsappUrl,
            'public_qr' => $this->qrUrl($publicUrl),
            'whatsapp_qr' => $whatsappUrl ? $this->qrUrl($whatsappUrl) : null,
            'entry_message' => $entryMessage,
            'checklist' => $checklist,
            'completed' => $completed,
            'total' => count($checklist),
            'percent' => (int) round(($completed / count($checklist)) * 100),
            'readiness' => $this->readiness($completed, count($checklist)),
            'next_action' => collect($checklist)->firstWhere('complete', false),
            'messages' => $this->messages($business, $publicUrl, $whatsappUrl),
        ];
    }

    private function checklist(Business $business, array $publicBooking, array $whatsapp, string $phone): array
    {
        $activeServices = $business->services()->where('is_active', true)->count();
        $activeProfessionals = $business->professionals()->where('is_active', true)->count();
        $professionalsWithServices = $business->professionals()
            ->where('is_active', true)
            ->whereHas('services')
            ->count();
        $openScheduleDays = $business->schedules()
            ->where('is_closed', false)
            ->whereNotNull('opens_at')
            ->whereNotNull('closes_at')
            ->count();

        return [
            [
                'label' => 'Perfil del negocio',
                'description' => 'Nombre, industria y contacto publico listos.',
                'why' => 'Permite que clientes reconozcan el negocio y sepan como contactarlo.',
                'complete' => filled($business->name) && (filled($business->phone) || filled($business->email)),
                'action' => route('settings.index'),
                'action_label' => 'Completar perfil',
                'group' => 'Base',
            ],
            [
                'label' => 'Servicios activos',
                'description' => $activeServices.' servicios disponibles para reservar.',
                'why' => 'Sin servicios, el cliente no sabe que puede reservar.',
                'complete' => $activeServices > 0,
                'action' => route('servicios.index'),
                'action_label' => 'Crear servicios',
                'group' => 'Oferta',
            ],
            [
                'label' => 'Profesionales activos',
                'description' => $activeProfessionals.' profesionales activos en la agenda.',
                'why' => 'Define quien atiende cada cita y habilita disponibilidad real.',
                'complete' => $activeProfessionals > 0,
                'action' => route('profesionales.index'),
                'action_label' => 'Crear equipo',
                'group' => 'Equipo',
            ],
            [
                'label' => 'Servicios asignados',
                'description' => $professionalsWithServices.' profesionales vinculados a servicios.',
                'why' => 'Evita que una reserva llegue a alguien que no presta ese servicio.',
                'complete' => $professionalsWithServices > 0,
                'action' => route('profesionales.index'),
                'action_label' => 'Asignar servicios',
                'group' => 'Equipo',
            ],
            [
                'label' => 'Horarios definidos',
                'description' => $openScheduleDays.' dias abiertos para disponibilidad.',
                'why' => 'TREBBIA usa estos horarios para aceptar o rechazar reservas.',
                'complete' => $openScheduleDays > 0,
                'action' => route('schedules.edit'),
                'action_label' => 'Definir horarios',
                'group' => 'Agenda',
            ],
            [
                'label' => 'Reserva publica',
                'description' => 'Pagina publica activa para recibir solicitudes web.',
                'why' => 'Crea el enlace que puedes enviar a clientes o poner en redes.',
                'complete' => (bool) ($publicBooking['allow_public_booking'] ?? false),
                'action' => route('settings.index').'#agenda-preferences',
                'action_label' => 'Activar reserva',
                'group' => 'Canales',
            ],
            [
                'label' => 'WhatsApp comercial',
                'description' => 'Numero, enlace y QR listos para compartir.',
                'why' => 'Convierte WhatsApp en la puerta de entrada a tus reservas.',
                'complete' => (bool) ($whatsapp['enabled'] ?? false) && filled($phone),
                'action' => route('settings.index').'#whatsapp-channel',
                'action_label' => 'Configurar WhatsApp',
                'group' => 'Canales',
            ],
            [
                'label' => 'Prueba de reserva',
                'description' => $business->appointments()->count().' citas creadas en TREBBIA.',
                'why' => 'Confirma que servicios, equipo y horarios funcionan en una reserva real.',
                'complete' => $business->appointments()->exists() || $business->bookingRequests()->exists(),
                'action' => route('agenda.create'),
                'action_label' => 'Crear prueba',
                'group' => 'Validacion',
            ],
        ];
    }

    private function readiness(int $completed, int $total): array
    {
        $percent = (int) round(($completed / max(1, $total)) * 100);

        if ($percent >= 100) {
            return [
                'title' => 'Listo para recibir reservas',
                'message' => 'Tu negocio ya tiene la configuracion base para operar y compartir sus canales.',
                'tone' => 'ready',
            ];
        }

        if ($percent >= 60) {
            return [
                'title' => 'Casi listo para salir a vender',
                'message' => 'Faltan pocos pasos para que el negocio pueda compartir reservas con confianza.',
                'tone' => 'progress',
            ];
        }

        return [
            'title' => 'Configuracion inicial en progreso',
            'message' => 'Completa los pasos base para que TREBBIA pueda calcular disponibilidad y recibir reservas.',
            'tone' => 'start',
        ];
    }

    private function messages(Business $business, string $publicUrl, ?string $whatsappUrl): array
    {
        return [
            'whatsapp_status' => "Hola, ya puedes reservar tu cita con {$business->name} desde este enlace: {$publicUrl}",
            'instagram_bio' => "Agenda tu cita con {$business->name}: {$publicUrl}",
            'manual_reply' => $whatsappUrl
                ? "Claro, puedes reservar aqui: {$publicUrl}\n\nTambien puedes escribirme por WhatsApp desde este enlace: {$whatsappUrl}"
                : "Claro, puedes reservar aqui: {$publicUrl}",
        ];
    }

    private function qrUrl(string $url): string
    {
        return 'https://api.qrserver.com/v1/create-qr-code/?size=260x260&data='.rawurlencode($url);
    }
}
