@php
    $isBusinessRecipient = $recipientType === 'business';
    $startsAt = $reservation->starts_at->timezone($business->timezone);
    $endsAt = $reservation->ends_at->timezone($business->timezone);
    $clientName = $reservation->client?->name ?? 'Cliente';
    $serviceName = $reservation->service?->name ?? 'Servicio';
    $professionalName = $reservation->professional?->name ?? 'Profesional';

    $headline = match ($event) {
        'booking_requested' => 'Nueva solicitud de reserva',
        'public_appointment_confirmed' => 'Nueva cita confirmada',
        'appointment_confirmed' => 'Tu cita fue confirmada',
        'booking_rejected' => 'Tu solicitud no fue aprobada',
        'appointment_cancelled' => 'Tu cita fue cancelada',
        'appointment_rescheduled' => 'Tu cita fue reprogramada',
        default => 'Actualización de reserva',
    };

    $intro = match ($event) {
        'booking_requested' => 'Un cliente acaba de solicitar una cita y necesita revisión del negocio.',
        'public_appointment_confirmed' => 'Un cliente acaba de reservar una cita y quedó confirmada automáticamente.',
        'appointment_confirmed' => "Hola {$clientName}, {$business->name} confirmó tu cita.",
        'booking_rejected' => "Hola {$clientName}, {$business->name} revisó tu solicitud y no pudo aprobarla.",
        'appointment_cancelled' => "Hola {$clientName}, {$business->name} canceló tu cita.",
        'appointment_rescheduled' => "Hola {$clientName}, {$business->name} reprogramó tu cita.",
        default => 'Hay una actualización relacionada con tu reserva.',
    };

    $actionUrl = $isBusinessRecipient
        ? route('dashboard')
        : route('public-booking.show', $business->slug);
    $actionText = $isBusinessRecipient ? 'Revisar en TREBBIA' : 'Crear nueva reserva';
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $headline }}</title>
</head>
<body style="margin:0;background:#f6f7f4;color:#18211f;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7f4;padding:24px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e1e6e0;border-radius:12px;padding:28px;">
                    <tr>
                        <td>
                            <p style="margin:0 0 8px;color:#245f57;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">TREBBIA</p>
                            <h1 style="margin:0;color:#0f172a;font-size:24px;line-height:1.25;">{{ $headline }}</h1>
                            <p style="margin:12px 0 0;color:#64748b;font-size:15px;line-height:1.6;">{{ $intro }}</p>

                            @if ($event === 'appointment_rescheduled' && $previousSchedule)
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:20px;border:1px solid #e8ddd0;border-radius:10px;background:#fffaf3;">
                                    <tr>
                                        <td style="padding:16px;font-size:14px;line-height:1.7;color:#4b5563;">
                                            <strong>Horario anterior:</strong><br>
                                            {{ $previousSchedule['starts_at']->timezone($business->timezone)->format('d/m/Y H:i') }}
                                            -
                                            {{ $previousSchedule['ends_at']->timezone($business->timezone)->format('H:i') }}
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:24px;border:1px solid #e1e6e0;border-radius:10px;background:#f8faf8;">
                                <tr>
                                    <td style="padding:18px;font-size:14px;line-height:1.8;color:#334155;">
                                        <strong>Negocio:</strong> {{ $business->name }}<br>
                                        <strong>Cliente:</strong> {{ $clientName }}<br>
                                        <strong>Servicio:</strong> {{ $serviceName }}<br>
                                        <strong>Profesional:</strong> {{ $professionalName }}<br>
                                        <strong>Fecha:</strong> {{ $startsAt->format('d/m/Y') }}<br>
                                        <strong>Hora:</strong> {{ $startsAt->format('H:i') }} - {{ $endsAt->format('H:i') }}<br>
                                        <strong>Estado:</strong>
                                        @switch($event)
                                            @case('booking_requested') Pendiente de aprobación @break
                                            @case('public_appointment_confirmed') Confirmada @break
                                            @case('appointment_confirmed') Confirmada @break
                                            @case('booking_rejected') No aprobada @break
                                            @case('appointment_cancelled') Cancelada @break
                                            @case('appointment_rescheduled') Reprogramada @break
                                            @default Actualizada
                                        @endswitch
                                        @if ($reservation->notes)
                                            <br><strong>Notas:</strong> {{ $reservation->notes }}
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0;">
                                <a href="{{ $actionUrl }}" style="display:inline-block;background:#24695f;color:#ffffff;text-decoration:none;font-weight:700;padding:13px 18px;border-radius:8px;">{{ $actionText }}</a>
                            </p>

                            <p style="margin:24px 0 0;color:#64748b;font-size:13px;line-height:1.6;">
                                Este correo fue enviado automáticamente desde Notificaciones TREBBIA.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
