<?php

namespace App\Libraries;

/**
 * Per-condominio allowed schedules for resident-announced deliveries and
 * proveedores, with a SEPARATE time window per weekday. Reads the
 * `condominios.horarios` JSON:
 *   {"delivery":{"activo":true,"dias":{"1":{"desde":"09:00","hasta":"18:00"}, ...}}}
 * A weekday present in `dias` is allowed (with its window); absent = not allowed.
 */
class Horario
{
    public const TIPOS = ['delivery', 'proveedor'];

    /** Weekday labels indexed by PHP date('w') (0=Sunday). */
    public const DIAS = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];

    /** Display order: Monday first. */
    private const ORDEN = [1, 2, 3, 4, 5, 6, 0];

    /** Decode the horarios JSON of a condominio row. @return array<string,mixed> */
    public static function config(?array $condominio): array
    {
        $raw = $condominio['horarios'] ?? null;
        if (! $raw) {
            return [];
        }
        $data = is_array($raw) ? $raw : json_decode($raw, true);

        return is_array($data) ? $data : [];
    }

    /**
     * Schedule for one tipo: enforced flag + per-weekday windows.
     * @return array{activo:bool,dias:array<int,array{desde:string,hasta:string}>}
     */
    public static function forTipo(?array $condominio, string $tipo): array
    {
        $c    = self::config($condominio)[$tipo] ?? [];
        $dias = [];
        foreach (($c['dias'] ?? []) as $d => $w) {
            $dias[(int) $d] = [
                'desde' => (string) ($w['desde'] ?? '00:00'),
                'hasta' => (string) ($w['hasta'] ?? '23:59'),
            ];
        }
        ksort($dias);

        return ['activo' => (bool) ($c['activo'] ?? false), 'dias' => $dias];
    }

    /**
     * Evaluate a tipo against its schedule at a timestamp.
     * @return array{enforced:bool,permitido:bool,mensaje:string,resumen:string}
     */
    public static function check(?array $condominio, string $tipo, ?int $ts = null): array
    {
        $cfg     = self::forTipo($condominio, $tipo);
        $resumen = self::resumen($cfg);

        if (! $cfg['activo']) {
            return ['enforced' => false, 'permitido' => true, 'mensaje' => '', 'resumen' => $resumen];
        }

        $zone = (! empty($condominio['timezone']) && Tz::valid($condominio['timezone'])) ? $condominio['timezone'] : Tz::DEFAULT;
        try {
            $dt = new \DateTime('now', new \DateTimeZone('UTC'));
            if ($ts !== null) {
                $dt->setTimestamp($ts);
            }
            $dt->setTimezone(new \DateTimeZone($zone));
        } catch (\Throwable $e) {
            return ['enforced' => true, 'permitido' => true, 'mensaje' => '', 'resumen' => $resumen];
        }
        $dow = (int) $dt->format('w');
        $hm  = $dt->format('H:i');

        $window    = $cfg['dias'][$dow] ?? null;
        $permitido = $window !== null && $hm >= $window['desde'] && $hm <= $window['hasta'];

        return [
            'enforced'  => true,
            'permitido' => $permitido,
            'mensaje'   => $permitido ? '' : 'Fuera del horario permitido para ' . $tipo . '. ' . $resumen,
            'resumen'   => $resumen,
        ];
    }

    /** Human-readable per-day summary, e.g. "Lun 09:00–18:00 · Sáb 10:00–14:00". */
    public static function resumen(array $cfg): string
    {
        if (! ($cfg['activo'] ?? false)) {
            return 'Sin restricción de horario.';
        }
        if (($cfg['dias'] ?? []) === []) {
            return 'Sin días permitidos.';
        }

        $parts = [];
        foreach (self::ORDEN as $d) {
            if (isset($cfg['dias'][$d])) {
                $parts[] = self::DIAS[$d] . ' ' . $cfg['dias'][$d]['desde'] . '–' . $cfg['dias'][$d]['hasta'];
            }
        }

        return implode(' · ', $parts);
    }
}
