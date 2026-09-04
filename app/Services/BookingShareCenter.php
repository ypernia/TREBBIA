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
                'complete' => filled($business->name) && (filled($business->phone) || filled($business->email)),
                'action' => route('settings.index'),
            ],
            [
                'label' => 'Servicios activos',
                'description' => $activeServices.' servicios disponibles para reservar.',
                'complete' => $activeServices > 0,
                'action' => route('servicios.index'),
            ],
            [
                'label' => 'Profesionales activos',
                'description' => $activeProfessionals.' profesionales activos en la agenda.',
                'complete' => $activeProfessionals > 0,
                'action' => route('profesionales.index'),
            ],
            [
                'label' => 'Servicios asignados',
                'description' => $professionalsWithServices.' profesionales vinculados a servicios.',
                'complete' => $professionalsWithServices > 0,
                'action' => route('profesionales.index'),
            ],
            [
                'label' => 'Horarios definidos',
                'description' => $openScheduleDays.' dias abiertos para disponibilidad.',
                'complete' => $openScheduleDays > 0,
                'action' => route('schedules.edit'),
            ],
            [
                'label' => 'Reserva publica',
                'description' => 'Pagina publica activa para recibir solicitudes web.',
                'complete' => (bool) ($publicBooking['allow_public_booking'] ?? false),
                'action' => route('settings.index').'#agenda-preferences',
            ],
            [
                'label' => 'WhatsApp comercial',
                'description' => 'Numero, enlace y QR listos para compartir.',
                'complete' => (bool) ($whatsapp['enabled'] ?? false) && filled($phone),
                'action' => route('settings.index').'#whatsapp-channel',
            ],
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
