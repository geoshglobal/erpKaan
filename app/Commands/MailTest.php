<?php

namespace App\Commands;

use App\Libraries\Mailer;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * Quick SMTP sanity check for the F2.4 email channel.
 *   php spark mail:test destinatario@ejemplo.com
 * Requires `notify.email = true` in .env (otherwise Mailer is a no-op).
 */
class MailTest extends BaseCommand
{
    protected $group       = 'Kaan';
    protected $name        = 'mail:test';
    protected $description = 'Envía un correo de prueba usando la config mail.* del .env.';
    protected $usage       = 'mail:test <destinatario>';
    protected $arguments   = ['destinatario' => 'Correo que recibirá la prueba'];

    public function run(array $params): void
    {
        $to = $params[0] ?? CLI::prompt('Destinatario');
        if (! $to) {
            CLI::error('Falta el destinatario.');
            return;
        }

        if (! Mailer::enabled()) {
            CLI::error('El canal de email está desactivado. Pon notify.email = true en .env para probar.');
            return;
        }

        CLI::write('Enviando a ' . $to . ' vía ' . env('mail.outgoingServer') . ':' . env('mail.outgoingPort') . ' ...', 'yellow');

        $html = Mailer::layout(
            'Prueba de correo · Kaan',
            "Si ves este mensaje, el canal de email SMTP quedó configurado correctamente.\nEnviado desde php spark mail:test.",
            site_url('dashboard'),
            'Abrir Kaan'
        );

        $ok = Mailer::send($to, 'Prueba de correo · Kaan', $html, 'Prueba de correo de Kaan (SMTP OK).');

        if ($ok) {
            CLI::write('✅ Correo enviado.', 'green');
        } else {
            CLI::error('❌ No se pudo enviar. Revisa writable/logs/ para el detalle SMTP.');
        }
    }
}
