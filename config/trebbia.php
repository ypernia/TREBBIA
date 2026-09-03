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
];
