<?php

namespace App\Libraries;

use App\Libraries\Mailer;
use App\Libraries\NotifPrefs;
use App\Libraries\Push;
use App\Models\NotificacionModel;
use App\Models\PersonaModel;

/**
 * Creates in-app notifications and mirrors them to the email (`notify.email`)
 * and Web Push (`notify.push`) channels when enabled — see App\Libraries\Mailer
 * and App\Libraries\Push. The recipient of an access notification is the visit's
 * requester (solicitante).
 */
class Notify
{
    /** Notify the requester of an acceso about an event on it. */
    public static function acceso(array $acceso, string $titulo, string $mensaje, ?string $url = null): void
    {
        $personaId = $acceso['solicitante_persona_id'] ?? null;
        if (! $personaId) {
            return;
        }

        $persona = (new PersonaModel())->find($personaId);
        if ($persona === null) {
            return;
        }

        $model = new NotificacionModel();
        $id    = $model->insert([
            'condominio_id' => $acceso['condominio_id'],
            'persona_id'    => $personaId,
            'user_id'       => $persona['user_id'] ?: null,
            'acceso_id'     => $acceso['id'],
            'tipo'          => 'acceso',
            'titulo'        => $titulo,
            'mensaje'       => $mensaje,
            'url'           => $url,
            'canal'         => 'in_app',
        ]);

        $userId = (int) ($persona['user_id'] ?? 0);

        // Email + push each fire only when the user hasn't opted out of that channel.
        if (NotifPrefs::email($userId)) {
            self::email($model, (int) $id, $persona['email'] ?? null, $titulo, $mensaje, $url);
        }

        if ($userId > 0 && NotifPrefs::push($userId)) {
            Push::toUser($userId, $titulo, $mensaje, $url);
        }
    }

    /**
     * Notify the caseta operators of a condominio about an acceso (e.g. a resident
     * announcing an expected delivery). In-app + push per each operator's prefs.
     */
    public static function caseta(array $acceso, string $titulo, string $mensaje, ?string $url = null): void
    {
        $userIds = self::casetaUserIds((int) $acceso['condominio_id']);
        if ($userIds === []) {
            return;
        }

        $model = new NotificacionModel();
        foreach ($userIds as $uid) {
            $model->insert([
                'condominio_id' => $acceso['condominio_id'],
                'persona_id'    => null,
                'user_id'       => $uid,
                'acceso_id'     => $acceso['id'],
                'tipo'          => 'acceso',
                'titulo'        => $titulo,
                'mensaje'       => $mensaje,
                'url'           => $url,
                'canal'         => 'in_app',
            ]);

            if (NotifPrefs::push($uid)) {
                Push::toUser($uid, $titulo, $mensaje, $url);
            }
        }
    }

    /** User ids of the caseta operators scoped to a condominio. @return list<int> */
    private static function casetaUserIds(int $condominioId): array
    {
        $rows = db_connect()->table('condominio_usuarios cu')
            ->select('cu.user_id')
            ->join('auth_groups_users g', 'g.user_id = cu.user_id', 'inner')
            ->where('g.group', 'caseta')
            ->where('cu.condominio_id', $condominioId)
            ->where('cu.activo', 1)
            ->where('cu.deleted_at', null)
            ->groupBy('cu.user_id')
            ->get()->getResultArray();

        return array_map(static fn ($r): int => (int) $r['user_id'], $rows);
    }

    /**
     * Resolve a stored notification URL to a single absolute URL — idempotent:
     * an already-absolute URL is returned unchanged (so legacy rows that stored
     * site_url() output don't get the base prepended twice), a relative path is
     * expanded with site_url().
     */
    public static function absUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        return preg_match('#^https?://#i', $url) ? $url : site_url($url);
    }

    /**
     * Mirror a just-created notification to email (best-effort). Stamps the row
     * with the send outcome. No-op unless the channel is enabled and the
     * recipient has an email address.
     */
    private static function email(NotificacionModel $model, int $notifId, ?string $to, string $titulo, string $mensaje, ?string $url): void
    {
        if ($notifId <= 0 || ! Mailer::enabled() || ! $to) {
            return;
        }

        $absUrl = self::absUrl($url);
        $html   = Mailer::layout($titulo, $mensaje, $absUrl, 'Ver en Kaan');

        try {
            $ok = Mailer::send($to, $titulo, $html, $mensaje . ($absUrl ? "\n\n" . $absUrl : ''));
            $model->update($notifId, $ok
                ? ['email_enviado_at' => date('Y-m-d H:i:s'), 'email_error' => null]
                : ['email_error' => 'no enviado']);
        } catch (\Throwable $e) {
            log_message('error', 'Notify email failed: ' . $e->getMessage());
            $model->update($notifId, ['email_error' => substr($e->getMessage(), 0, 250)]);
        }
    }
}
