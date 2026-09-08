@php
    $isRequest = $reservation instanceof \App\Models\BookingRequest;
    $dashboardUrl = route('dashboard');
    $agendaUrl = route('agenda.index', ['date' => $reservation->starts_at->toDateString()]);
@endphp

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $isRequest ? 'Nueva solicitud de reserva' : 'Nueva cita confirmada' }}</title>
</head>
<body style="margin:0;background:#f6f7f4;color:#18211f;font-family:Arial,sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f6f7f4;padding:24px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;background:#ffffff;border:1px solid #e1e6e0;border-radius:12px;padding:28px;">
                    <tr>
                        <td>
                            <p style="margin:0 0 8px;color:#245f57;font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:1px;">TREBBIA</p>
                            <h1 style="margin:0;color:#0f172a;font-size:24px;line-height:1.25;">
                                {{ $isRequest ? 'Nueva solicitud de reserva' : 'Nueva cita confirmada' }}
                            </h1>
                            <p style="margin:12px 0 0;color:#64748b;font-size:15px;line-height:1.6;">
                                {{ $isRequest ? 'Un cliente acaba de solicitar una cita y necesita revisión del negocio.' : 'Un cliente acaba de reservar una cita y quedó confirmada automáticamente.' }}
                            </p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:24px;border:1px solid #e1e6e0;border-radius:10px;background:#f8faf8;">
                                <tr>
                                    <td style="padding:18px;font-size:14px;line-height:1.8;color:#334155;">
                                        <strong>Negocio:</strong> {{ $business->name }}<br>
                                        <strong>Cliente:</strong> {{ $reservation->client?->name ?? 'Sin nombre' }}<br>
                                        <strong>Servicio:</strong> {{ $reservation->service?->name ?? 'Sin servicio' }}<br>
                                        <strong>Profesional:</strong> {{ $reservation->professional?->name ?? 'Sin profesional' }}<br>
                                        <strong>Fecha:</strong> {{ $reservation->starts_at->timezone($business->timezone)->format('d/m/Y') }}<br>
                                        <strong>Hora:</strong> {{ $reservation->starts_at->timezone($business->timezone)->format('H:i') }} - {{ $reservation->ends_at->timezone($business->timezone)->format('H:i') }}<br>
                                        <strong>Estado:</strong> {{ $isRequest ? 'Pendiente de aprobación' : 'Confirmada' }}
                                        @if ($reservation->notes)
                                            <br><strong>Notas:</strong> {{ $reservation->notes }}
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0;">
                                <a href="{{ $isRequest ? $dashboardUrl : $agendaUrl }}" style="display:inline-block;background:#24695f;color:#ffffff;text-decoration:none;font-weight:700;padding:13px 18px;border-radius:8px;">
                                    {{ $isRequest ? 'Revisar solicitud' : 'Ver en agenda' }}
                                </a>
                            </p>

                            <p style="margin:24px 0 0;color:#64748b;font-size:13px;line-height:1.6;">
                                Recibes este aviso porque las notificaciones por correo están activas en la configuración de agenda de este negocio.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
