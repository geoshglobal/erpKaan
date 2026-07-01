<?php

namespace App\Libraries;

use App\Models\NotificacionModel;
use App\Models\PersonaModel;

/**
 * Creates in-app notifications. Email/push channels are planned for later;
 * for now everything is 'in_app'. The recipient of an access notification is
 * the visit's requester (solicitante).
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

        (new NotificacionModel())->insert([
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
    }
}
