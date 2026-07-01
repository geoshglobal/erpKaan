<?php

namespace App\Libraries;

/**
 * Per-user notification channel preferences, stored via CodeIgniter Settings
 * with context "user:{id}". Defaults come from Config\Notificaciones (both on).
 * These preferences layer ON TOP of the global env flags (notify.email /
 * notify.push) and the browser push subscription — a channel fires only when
 * BOTH the global flag is on AND the user hasn't opted out.
 */
class NotifPrefs
{
    private static function ctx(int $userId): string
    {
        return 'user:' . $userId;
    }

    public static function email(int $userId): bool
    {
        if ($userId <= 0) {
            return true; // personas without a login account still get email
        }

        return (bool) service('settings')->get('Notificaciones.email', self::ctx($userId));
    }

    public static function push(int $userId): bool
    {
        if ($userId <= 0) {
            return false;
        }

        return (bool) service('settings')->get('Notificaciones.push', self::ctx($userId));
    }

    /** User's preferred timezone, or '' to inherit the condominio's. */
    public static function timezone(int $userId): string
    {
        if ($userId <= 0) {
            return '';
        }

        return (string) service('settings')->get('Notificaciones.timezone', self::ctx($userId));
    }

    /** @return array{email: bool, push: bool, timezone: string} */
    public static function all(int $userId): array
    {
        return [
            'email'    => self::email($userId),
            'push'     => self::push($userId),
            'timezone' => self::timezone($userId),
        ];
    }

    public static function save(int $userId, bool $email, bool $push, string $timezone = ''): void
    {
        if ($userId <= 0) {
            return;
        }
        $settings = service('settings');
        $settings->set('Notificaciones.email', $email, self::ctx($userId));
        $settings->set('Notificaciones.push', $push, self::ctx($userId));
        $settings->set('Notificaciones.timezone', $timezone, self::ctx($userId));
    }
}
