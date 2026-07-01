<?php

namespace App\Libraries;

use Config\Email as EmailConfig;

/**
 * Thin SMTP sender built from the `mail.*` keys in `.env`
 * (outgoing kaan.geoshglobal.com:465, smtp/SSL). Wraps CI4's Email service so
 * the rest of the app never touches SMTP config directly.
 *
 * Sending is gated by `notify.email` in `.env`: when falsy (default), send()
 * is a no-op that returns false — so local dev / tests never hit the SMTP box.
 */
class Mailer
{
    /** Whether the email channel is enabled (env flag). */
    public static function enabled(): bool
    {
        return filter_var(env('notify.email', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Send an HTML email. Returns true on success. When the channel is disabled
     * or SMTP credentials are missing, returns false without throwing.
     */
    public static function send(string $to, string $subject, string $htmlBody, ?string $altBody = null): bool
    {
        if (! self::enabled()) {
            return false;
        }

        $host = env('mail.outgoingServer');
        $user = env('mail.username');
        $pass = env('mail.password');
        if (! $host || ! $user || ! $pass || ! $to) {
            return false;
        }

        $port   = (int) (env('mail.outgoingPort') ?: 465);
        $crypto = $port === 465 ? 'ssl' : 'tls'; // 465 → implicit SSL, 587 → STARTTLS

        $config             = new EmailConfig();
        $config->protocol   = 'smtp';
        $config->SMTPHost   = $host;
        $config->SMTPUser   = $user;
        $config->SMTPPass   = $pass;
        $config->SMTPPort   = $port;
        $config->SMTPCrypto = $crypto;
        $config->SMTPTimeout = 15;
        $config->fromEmail  = env('mail.fromEmail', $user);
        $config->fromName   = env('mail.fromName', 'Kaan');
        $config->mailType   = 'html';
        $config->charset    = 'UTF-8';
        $config->wordWrap   = true;

        $email = \Config\Services::email($config);
        $email->setFrom($config->fromEmail, $config->fromName);
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($htmlBody);
        if ($altBody !== null) {
            $email->setAltMessage($altBody);
        }

        if ($email->send(false)) {
            return true;
        }

        // Surface the SMTP failure in the logs, but never break the request flow.
        log_message('error', 'Mailer send failed: ' . $email->printDebugger(['headers']));

        return false;
    }

    /**
     * Wrap plain title/message content in the shared HTML shell.
     */
    public static function layout(string $titulo, string $mensaje, ?string $url = null, ?string $ctaLabel = null): string
    {
        $safeTitulo  = esc($titulo);
        $safeMensaje = nl2br(esc($mensaje));
        $cta         = '';
        if ($url) {
            $label = esc($ctaLabel ?? 'Ver detalle');
            $cta   = '<p style="margin:1.5rem 0 0;"><a href="' . esc($url, 'attr') . '"'
                . ' style="display:inline-block; background:#2C6E52; color:#fff; text-decoration:none;'
                . ' padding:.6rem 1.2rem; border-radius:8px; font-weight:700;">' . $label . '</a></p>';
        }

        $logo = esc(site_url('brand/png/erpKaan-isotipo-64.png'), 'attr');

        return '<!doctype html><html lang="es"><body style="margin:0; background:#F6F4ED; font-family:'
            . '-apple-system,Segoe UI,Roboto,Helvetica,Arial,sans-serif; color:#1C2621;">'
            . '<div style="max-width:520px; margin:0 auto; padding:1.5rem;">'
            . '<div style="background:#fff; border-radius:14px; padding:1.5rem 1.6rem; border:1px solid #dbe5de;">'
            . '<div style="display:flex; align-items:center; gap:.5rem; margin-bottom:.9rem;">'
            . '<img src="' . $logo . '" alt="" width="28" height="28" style="border-radius:7px;">'
            . '<span style="font-weight:800; color:#2C6E52; font-size:1.05rem;">erpKaan</span></div>'
            . '<h1 style="font-size:1.15rem; margin:0 0 .6rem; color:#1C2621;">' . $safeTitulo . '</h1>'
            . '<div style="font-size:.95rem; line-height:1.5; color:#3a4a41;">' . $safeMensaje . '</div>'
            . $cta
            . '</div>'
            . '<p style="text-align:center; color:#6b7a70; font-size:.75rem; margin-top:1rem;">'
            . 'Este es un mensaje automático de la administración de tu condominio.</p>'
            . '</div></body></html>';
    }
}
