<?php

namespace App\Libraries;

/**
 * Timezone resolution + display. Datetimes are STORED in UTC (the server clock);
 * they are rendered in the resolved zone: user preference → active condominio's
 * timezone → app default. Storage is never changed here.
 */
class Tz
{
    public const DEFAULT = 'America/Mexico_City';

    /** Common Mexican zones offered in selectors. @return array<string,string> */
    public const ZONES = [
        'America/Mexico_City' => 'Centro (CDMX, Guadalajara, Monterrey)',
        'America/Cancun'      => 'Sureste (Quintana Roo)',
        'America/Merida'      => 'Sureste (Yucatán/Campeche)',
        'America/Monterrey'   => 'Noreste (Monterrey)',
        'America/Chihuahua'   => 'Pacífico (Chihuahua)',
        'America/Mazatlan'    => 'Pacífico (Sinaloa/Nayarit)',
        'America/Tijuana'     => 'Noroeste (Baja California)',
        'America/Hermosillo'  => 'Noroeste (Sonora, sin horario de verano)',
        'UTC'                 => 'UTC',
    ];

    /** Resolve the timezone for the current request. */
    public static function current(): string
    {
        if (function_exists('auth') && auth()->loggedIn()) {
            $userTz = (string) service('settings')->get('Notificaciones.timezone', 'user:' . auth()->id());
            if ($userTz !== '' && self::valid($userTz)) {
                return $userTz;
            }
        }

        $condo = service('tenant')->active();
        if (! empty($condo['timezone']) && self::valid($condo['timezone'])) {
            return $condo['timezone'];
        }

        return self::DEFAULT;
    }

    /** Format a UTC-stored datetime string in the resolved (or given) timezone. */
    public static function disp(?string $utc, string $fmt = 'd/m/Y H:i', ?string $zone = null): string
    {
        if (! $utc) {
            return '';
        }
        try {
            $dt = new \DateTime($utc, new \DateTimeZone('UTC'));
            $dt->setTimezone(new \DateTimeZone($zone ?? self::current()));

            return $dt->format($fmt);
        } catch (\Throwable $e) {
            return (string) $utc;
        }
    }

    public static function valid(string $zone): bool
    {
        return in_array($zone, \DateTimeZone::listIdentifiers(), true);
    }

    /** Local date (Y-m-d) at start/end of day, converted to a UTC 'Y-m-d H:i:s'. */
    public static function boundary(?string $localDate, bool $end = false, ?string $zone = null): ?string
    {
        if (! $localDate) {
            return null;
        }
        try {
            $dt = new \DateTime($localDate . ($end ? ' 23:59:59' : ' 00:00:00'), new \DateTimeZone($zone ?? self::current()));
            $dt->setTimezone(new \DateTimeZone('UTC'));

            return $dt->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Local date (Y-m-d) N days ago (0 = today) in the resolved zone. */
    public static function localDate(int $daysAgo = 0, ?string $zone = null): string
    {
        try {
            $dt = new \DateTime('now', new \DateTimeZone($zone ?? self::current()));
            if ($daysAgo > 0) {
                $dt->modify("-{$daysAgo} days");
            }

            return $dt->format('Y-m-d');
        } catch (\Throwable $e) {
            return date('Y-m-d');
        }
    }
}
