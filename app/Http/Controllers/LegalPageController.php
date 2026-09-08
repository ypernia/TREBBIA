<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LegalPageController extends Controller
{
    public function terms(): View
    {
        return view('legal.show', [
            'title' => 'Términos y condiciones',
            'updatedAt' => '08/09/2026',
            'intro' => 'Estas condiciones regulan el acceso y uso de TREBBIA como plataforma SaaS de reservas, agenda, clientes y canales de atención para negocios.',
            'sections' => [
                [
                    'title' => 'Uso de la plataforma',
                    'body' => 'TREBBIA permite a negocios configurar servicios, profesionales, horarios, clientes, reservas públicas y flujos de atención. Cada negocio es responsable de la información que registra y de mantener actualizados sus datos operativos.',
                ],
                [
                    'title' => 'Cuentas y permisos',
                    'body' => 'El acceso se realiza mediante usuarios autenticados. Los permisos se asignan por negocio y rol. El usuario administrador del negocio debe cuidar sus credenciales y asignar accesos solo a personas autorizadas.',
                ],
                [
                    'title' => 'Reservas y disponibilidad',
                    'body' => 'TREBBIA ayuda a organizar disponibilidad y solicitudes de reserva. La confirmación final de una cita puede depender de la configuración de cada negocio, sus horarios, recursos y políticas internas.',
                ],
                [
                    'title' => 'Planes y pagos',
                    'body' => 'El acceso a funcionalidades puede depender del plan contratado, límites de uso, estado del trial o activación de membresía. Los valores comerciales pueden actualizarse y se informarán por los canales oficiales de TREBBIA.',
                ],
                [
                    'title' => 'Responsabilidad del negocio',
                    'body' => 'El negocio debe usar la plataforma conforme a la ley aplicable, contar con autorizaciones necesarias de sus clientes y evitar registrar información que no sea necesaria para su operación.',
                ],
            ],
        ]);
    }

    public function privacy(): View
    {
        return view('legal.show', [
            'title' => 'Política de privacidad',
            'updatedAt' => '08/09/2026',
            'intro' => 'TREBBIA protege la información registrada por los negocios y busca que el tratamiento de datos sea claro, reservado y controlado.',
            'sections' => [
                [
                    'title' => 'Información que se registra',
                    'body' => 'La plataforma puede almacenar datos del negocio, usuarios internos, servicios, profesionales, agenda, clientes, solicitudes de reserva, mensajes operativos y configuraciones de canales como WhatsApp.',
                ],
                [
                    'title' => 'Uso de la información',
                    'body' => 'La información se utiliza para prestar el servicio, permitir la gestión de reservas, mostrar disponibilidad, conservar historiales operativos, generar reportes y mejorar la experiencia del negocio.',
                ],
                [
                    'title' => 'Reserva y confidencialidad',
                    'body' => 'La información de clientes y pacientes es tratada como reservada. TREBBIA no la vende ni la comparte con terceros para fines ajenos a la prestación del servicio.',
                ],
                [
                    'title' => 'Acceso de soporte',
                    'body' => 'El acceso de soporte a información sensible debe ser limitado, autorizado, justificado y auditable. El superadmin de la plataforma no debe consultar información sensible sin una razón de soporte controlada.',
                ],
                [
                    'title' => 'Conservacion de datos',
                    'body' => 'Los datos se conservan mientras el negocio mantenga una cuenta o mientras sea necesario para cumplir obligaciones operativas, contractuales o legales aplicables.',
                ],
            ],
        ]);
    }

    public function dataProcessing(): View
    {
        return view('legal.show', [
            'title' => 'Tratamiento de datos',
            'updatedAt' => '08/09/2026',
            'intro' => 'Este documento resume cómo TREBBIA entiende el manejo de datos personales dentro de la operación de reservas y agendamiento.',
            'sections' => [
                [
                    'title' => 'Rol del negocio',
                    'body' => 'Cada negocio decide qué datos solicita a sus clientes, pacientes o usuarios finales. Por eso, el negocio debe contar con las autorizaciones necesarias para registrar y usar esa información.',
                ],
                [
                    'title' => 'Rol de TREBBIA',
                    'body' => 'TREBBIA actúa como plataforma tecnológica para almacenar, organizar y procesar información necesaria para la agenda, reservas, seguimiento operativo y comunicaciones configuradas por el negocio.',
                ],
                [
                    'title' => 'Datos sensibles',
                    'body' => 'En industrias como salud, fisioterapia, estética, bienestar o veterinaria puede registrarse información sensible. Su acceso debe limitarse a usuarios autorizados del negocio y a soporte controlado cuando aplique.',
                ],
                [
                    'title' => 'Seguridad operativa',
                    'body' => 'La plataforma separa la información por negocio, valida permisos desde backend y mantiene controles para reducir accesos no autorizados entre empresas.',
                ],
                [
                    'title' => 'Solicitud de información',
                    'body' => 'Los clientes finales deben dirigir sus solicitudes de actualización, corrección o eliminación de datos al negocio que registró la información, sin perjuicio del apoyo técnico que TREBBIA pueda prestar.',
                ],
            ],
        ]);
    }
}
