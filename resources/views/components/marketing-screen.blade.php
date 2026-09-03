@props([
    'screen' => 'dashboard',
])

@php
    $screens = [
        'dashboard' => [
            'title' => 'Dashboard',
            'subtitle' => 'Reservas, clientes y canales',
            'metrics' => [['18', 'Citas hoy'], ['246', 'Clientes'], ['8', 'Profesionales']],
            'rows' => ['WhatsApp: valoración inicial', 'Enlace público: sesión reservada', 'Recepción: cambio de horario'],
        ],
        'agenda' => [
            'title' => 'Agenda',
            'subtitle' => 'Jueves 03/09/2026',
            'metrics' => [['08', 'Hoy'], ['14', 'Confirmadas'], ['4', 'Canales']],
            'rows' => ['08:00 · Laura Méndez · Valoración inicial', '10:30 · Andrés Rojas · Fisioterapia', '14:00 · Camila Soto · Masaje terapéutico'],
        ],
        'clientes' => [
            'title' => 'Clientes',
            'subtitle' => 'Historial y contacto centralizado',
            'metrics' => [['246', 'Activos'], ['38', 'Nuevos'], ['92%', 'Contacto']],
            'rows' => ['María Fernanda Ruiz · Hoy', 'Carlos Pineda · Mañana', 'Natalia Gómez · Seguimiento'],
        ],
        'equipo' => [
            'title' => 'Profesionales',
            'subtitle' => 'Equipo, servicios y disponibilidad',
            'metrics' => [['8', 'Activos'], ['32', 'Servicios'], ['5', 'Sedes']],
            'rows' => ['Laura Méndez · Fisioterapeuta', 'Andrés Rojas · Terapeuta deportivo', 'Camila Soto · Bienestar integral'],
        ],
        'configuracion' => [
            'title' => 'Configuración',
            'subtitle' => 'Negocio, horarios y reservas públicas',
            'metrics' => [['30', 'Min'], ['2h', 'Aviso'], ['ON', 'Link']],
            'rows' => ['Perfil del negocio actualizado', 'Reserva pública activa', 'Usuarios y roles configurados'],
        ],
    ];

    $data = $screens[$screen] ?? $screens['dashboard'];
@endphp

<svg {{ $attributes->merge(['class' => 'block h-auto w-full']) }} viewBox="0 0 1200 760" fill="none" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="{{ $data['title'] }} de TREBBIA">
    <rect width="1200" height="760" rx="28" fill="#FBFAF7" />
    <rect x="30" y="30" width="1140" height="700" rx="26" fill="#FFFFFF" stroke="#DFE8E5" stroke-width="2" />
    <rect x="30" y="30" width="1140" height="58" rx="26" fill="#F2F7F6" />
    <circle cx="72" cy="60" r="8" fill="#73D7CD" />
    <circle cx="100" cy="60" r="8" fill="#DCEFFA" />
    <circle cx="128" cy="60" r="8" fill="#245B63" />

    <rect x="70" y="126" width="178" height="560" rx="20" fill="#F7F5EF" />
    <text x="100" y="175" fill="#245B63" font-family="Avenir Next, Nunito Sans, Arial, sans-serif" font-size="29" font-weight="700">trebbia</text>
    <rect x="100" y="230" width="118" height="32" rx="10" fill="#E5F7F5" />
    <rect x="100" y="292" width="102" height="14" rx="7" fill="#66747E" opacity=".5" />
    <rect x="100" y="340" width="122" height="14" rx="7" fill="#66747E" opacity=".35" />
    <rect x="100" y="388" width="104" height="14" rx="7" fill="#66747E" opacity=".35" />
    <rect x="100" y="436" width="128" height="14" rx="7" fill="#66747E" opacity=".35" />

    <text x="300" y="172" fill="#1F2933" font-family="Avenir Next, Nunito Sans, Arial, sans-serif" font-size="42" font-weight="700">{{ $data['title'] }}</text>
    <text x="300" y="210" fill="#66747E" font-family="Avenir Next, Nunito Sans, Arial, sans-serif" font-size="20" font-weight="400">{{ $data['subtitle'] }}</text>

    @foreach ($data['metrics'] as $metric)
        <rect x="{{ 300 + (($loop->iteration - 1) * 238) }}" y="258" width="205" height="118" rx="20" fill="#F2F7F6" stroke="#DFE8E5" />
        <text x="{{ 328 + (($loop->iteration - 1) * 238) }}" y="310" fill="#66747E" font-family="Avenir Next, Nunito Sans, Arial, sans-serif" font-size="17" font-weight="500">{{ $metric[1] }}</text>
        <text x="{{ 328 + (($loop->iteration - 1) * 238) }}" y="354" fill="#245B63" font-family="Avenir Next, Nunito Sans, Arial, sans-serif" font-size="40" font-weight="700">{{ $metric[0] }}</text>
    @endforeach

    <rect x="300" y="430" width="590" height="230" rx="22" fill="#FFFFFF" stroke="#DFE8E5" stroke-width="2" />
    @foreach ($data['rows'] as $row)
        <rect x="330" y="{{ 465 + (($loop->iteration - 1) * 58) }}" width="530" height="42" rx="13" fill="{{ $loop->even ? '#E5F7F5' : '#F2F7F6' }}" />
        <text x="352" y="{{ 492 + (($loop->iteration - 1) * 58) }}" fill="#1F2933" font-family="Avenir Next, Nunito Sans, Arial, sans-serif" font-size="17" font-weight="500">{{ $row }}</text>
    @endforeach

    <rect x="930" y="258" width="190" height="402" rx="24" fill="#245B63" />
    <text x="962" y="306" fill="#E5F7F5" font-family="Avenir Next, Nunito Sans, Arial, sans-serif" font-size="18" font-weight="600">Actividad</text>
    @foreach ([52, 78, 46, 88, 64] as $bar)
        <rect x="{{ 962 + (($loop->iteration - 1) * 30) }}" y="{{ 592 - ($bar * 2.8) }}" width="18" height="{{ $bar * 2.8 }}" rx="9" fill="{{ $loop->even ? '#DCEFFA' : '#73D7CD' }}" />
    @endforeach
    <circle cx="1030" cy="398" r="58" fill="#E5F7F5" opacity=".18" />
    <circle cx="1030" cy="398" r="34" fill="#73D7CD" />
</svg>
