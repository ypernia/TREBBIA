<?php

return [
    'roles' => [
        'owner' => 'Owner',
        'admin' => 'Admin',
        'receptionist' => 'Receptionist',
        'professional' => 'Professional',
    ],

    'modules' => [
        'agenda' => ['label' => 'Agenda', 'status' => 'Proximamente', 'summary' => 'Vista operativa para citas, bloqueos y disponibilidad.'],
        'clientes' => ['label' => 'Clientes', 'status' => 'Funcional', 'route' => 'clientes.index', 'summary' => 'Base de clientes con historial y datos de contacto.'],
        'servicios' => ['label' => 'Servicios', 'status' => 'Funcional', 'route' => 'servicios.index', 'summary' => 'Catalogo inicial preparado para servicios por empresa.'],
        'profesionales' => ['label' => 'Profesionales', 'status' => 'Funcional', 'route' => 'profesionales.index', 'summary' => 'Equipo asociado a sedes, horarios y futuras agendas.'],
        'recursos' => ['label' => 'Recursos', 'status' => 'Funcional', 'route' => 'recursos.index', 'summary' => 'Cabinas, equipos, consultorios u otros recursos reservables.'],
        'automatizaciones' => ['label' => 'Automatizaciones', 'status' => 'Proximamente', 'summary' => 'Flujos y recordatorios se integraran en fases posteriores.'],
        'reportes' => ['label' => 'Reportes', 'status' => 'Proximamente', 'summary' => 'Indicadores de reservas, clientes, ingresos y ocupacion.'],
        'membresia' => ['label' => 'Membresia', 'status' => 'Proximamente', 'summary' => 'Planes, suscripciones y limites comerciales.'],
        'configuracion' => ['label' => 'Configuracion', 'status' => 'Funcional', 'route' => 'schedules.edit', 'summary' => 'Preferencias del negocio, roles y parametros de agenda.'],
    ],
];
