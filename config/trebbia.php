<?php

return [
    'roles' => [
        'owner' => 'Owner',
        'admin' => 'Admin',
        'receptionist' => 'Receptionist',
        'professional' => 'Professional',
    ],

    'modules' => [
        'agenda' => ['label' => 'Agenda', 'status' => 'Funcional', 'route' => 'agenda.index', 'summary' => 'Vista operativa para citas, bloqueos y disponibilidad.'],
        'clientes' => ['label' => 'Clientes', 'status' => 'Funcional', 'route' => 'clientes.index', 'summary' => 'Base de clientes con historial y datos de contacto.'],
        'historia_clinica' => [
            'label' => 'Historia clinica',
            'status' => 'Funcional',
            'route' => 'clinical-records.index',
            'summary' => 'Evoluciones, diagnosticos, planes y recomendaciones por paciente.',
            'available_for_industries' => ['salud', 'fisioterapia', 'fisio', 'spa', 'veterinaria', 'estetica', 'medicina', 'odontologia', 'psicologia', 'terapia', 'rehabilitacion'],
        ],
        'servicios' => ['label' => 'Servicios', 'status' => 'Funcional', 'route' => 'servicios.index', 'summary' => 'Catalogo inicial preparado para servicios por empresa.'],
        'profesionales' => ['label' => 'Profesionales', 'status' => 'Funcional', 'route' => 'profesionales.index', 'summary' => 'Equipo asociado a sedes, horarios y futuras agendas.'],
        'recursos' => ['label' => 'Recursos', 'status' => 'Funcional', 'route' => 'recursos.index', 'summary' => 'Cabinas, equipos, consultorios u otros recursos reservables.'],
        'automatizaciones' => ['label' => 'Automatizaciones', 'status' => 'Funcional', 'route' => 'automations.index', 'summary' => 'Plantillas y recordatorios operativos para citas.'],
        'compartir' => ['label' => 'Compartir reservas', 'status' => 'Funcional', 'route' => 'sharing.index', 'summary' => 'Enlaces publicos, QR, mensajes sugeridos y checklist para salir a vender.'],
        'whatsapp_automatico' => ['label' => 'WhatsApp automatico', 'status' => 'Funcional', 'route' => 'whatsapp-activation.create', 'summary' => 'Solicitud administrada para que TREBBIA active WhatsApp automatico sin friccion para el negocio.'],
        'whatsapp_demo' => ['label' => 'WhatsApp demo', 'status' => 'Funcional', 'route' => 'whatsapp-simulator.index', 'summary' => 'Simulador interno para probar conversaciones y reservas sin conectar Meta.'],
        'reportes' => ['label' => 'Reportes', 'status' => 'Funcional', 'route' => 'reports.index', 'summary' => 'Indicadores de citas, ingresos estimados, servicios, profesionales y clientes.'],
        'membresia' => ['label' => 'Membresia', 'status' => 'Funcional', 'route' => 'membership.index', 'summary' => 'Plan actual, uso del negocio y limites comerciales.'],
        'configuracion' => ['label' => 'Configuracion', 'status' => 'Funcional', 'route' => 'settings.index', 'summary' => 'Perfil del negocio, sedes, usuarios base y parametros de agenda.'],
    ],

    'plans' => [
        [
            'name' => 'Inicial',
            'code' => 'starter',
            'monthly_price_cents' => 0,
            'limits' => [
                'monthly_appointments' => 50,
                'professionals' => 2,
                'services' => 5,
                'branches' => 1,
                'users' => 1,
                'public_booking' => true,
                'automations' => false,
            ],
            'features' => ['Agenda', 'Clientes', 'Reservas publicas'],
        ],
        [
            'name' => 'Profesional',
            'code' => 'professional',
            'monthly_price_cents' => 4900000,
            'limits' => [
                'monthly_appointments' => 300,
                'professionals' => 8,
                'services' => 25,
                'branches' => 3,
                'users' => 5,
                'public_booking' => true,
                'automations' => true,
            ],
            'features' => ['Agenda avanzada', 'Reportes', 'Automatizaciones', 'Reservas publicas'],
        ],
        [
            'name' => 'Empresa',
            'code' => 'business',
            'monthly_price_cents' => 12900000,
            'limits' => [
                'monthly_appointments' => null,
                'professionals' => null,
                'services' => null,
                'branches' => null,
                'users' => null,
                'public_booking' => true,
                'automations' => true,
            ],
            'features' => ['Uso ampliado', 'Multiples sedes', 'Equipo completo', 'Soporte prioritario'],
        ],
    ],

    'resource_presets' => [
        'default' => [
            ['name' => 'Sala 1', 'type' => 'Sala'],
            ['name' => 'Consultorio 1', 'type' => 'Consultorio'],
            ['name' => 'Equipo principal', 'type' => 'Equipo'],
        ],
        'fisioterapia' => [
            ['name' => 'Consultorio fisioterapia 1', 'type' => 'Consultorio'],
            ['name' => 'Camilla 1', 'type' => 'Equipo'],
            ['name' => 'Camilla 2', 'type' => 'Equipo'],
            ['name' => 'Sala de rehabilitacion', 'type' => 'Sala'],
            ['name' => 'Equipo de electroterapia', 'type' => 'Equipo'],
        ],
        'salud' => [
            ['name' => 'Consultorio 1', 'type' => 'Consultorio'],
            ['name' => 'Consultorio 2', 'type' => 'Consultorio'],
            ['name' => 'Sala de procedimientos', 'type' => 'Sala'],
            ['name' => 'Equipo de diagnostico', 'type' => 'Equipo'],
        ],
        'belleza' => [
            ['name' => 'Cabina 1', 'type' => 'Cabina'],
            ['name' => 'Cabina 2', 'type' => 'Cabina'],
            ['name' => 'Camilla estetica', 'type' => 'Equipo'],
            ['name' => 'Equipo facial', 'type' => 'Equipo'],
        ],
        'barberia' => [
            ['name' => 'Silla 1', 'type' => 'Puesto'],
            ['name' => 'Silla 2', 'type' => 'Puesto'],
            ['name' => 'Lavacabezas', 'type' => 'Equipo'],
        ],
        'restaurante' => [
            ['name' => 'Mesa 1', 'type' => 'Mesa'],
            ['name' => 'Mesa 2', 'type' => 'Mesa'],
            ['name' => 'Salon principal', 'type' => 'Salon'],
        ],
    ],
];
