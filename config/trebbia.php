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
        'servicios' => ['label' => 'Servicios', 'status' => 'Funcional', 'route' => 'servicios.index', 'summary' => 'Catalogo inicial preparado para servicios por empresa.'],
        'profesionales' => ['label' => 'Profesionales', 'status' => 'Funcional', 'route' => 'profesionales.index', 'summary' => 'Equipo asociado a sedes, horarios y futuras agendas.'],
        'recursos' => ['label' => 'Recursos', 'status' => 'Funcional', 'route' => 'recursos.index', 'summary' => 'Cabinas, equipos, consultorios u otros recursos reservables.'],
        'automatizaciones' => ['label' => 'Automatizaciones', 'status' => 'Funcional', 'route' => 'automations.index', 'summary' => 'Plantillas y recordatorios operativos para citas.'],
        'reportes' => ['label' => 'Reportes', 'status' => 'Funcional', 'route' => 'reports.index', 'summary' => 'Indicadores de citas, ingresos estimados, servicios, profesionales y clientes.'],
        'membresia' => ['label' => 'Membresia', 'status' => 'Proximamente', 'summary' => 'Planes, suscripciones y limites comerciales.'],
        'configuracion' => ['label' => 'Configuracion', 'status' => 'Funcional', 'route' => 'settings.index', 'summary' => 'Perfil del negocio, sedes, usuarios base y parametros de agenda.'],
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
