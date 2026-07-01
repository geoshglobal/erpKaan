<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Default notification channel preferences. Per-user overrides are stored via
 * CodeIgniter Settings with context "user:{id}" (see App\Libraries\NotifPrefs).
 * These defaults apply when a user hasn't chosen — both channels on.
 */
class Notificaciones extends BaseConfig
{
    /** Mirror in-app notifications to the user's email. */
    public bool $email = true;

    /** Send Web Push notifications to the user's subscribed browsers. */
    public bool $push = true;
}
