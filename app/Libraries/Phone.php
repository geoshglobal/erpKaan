<?php

namespace App\Libraries;

/**
 * Phone helpers. Defaults to Mexico (+52) when no country code is present.
 */
class Phone
{
    /** Digits in E.164 order without '+', adding the default country code if missing. */
    public static function e164(string $phone, string $cc = '52'): ?string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return null;
        }
        // 10-digit local MX number → prepend country code.
        if (strlen($digits) === 10) {
            return $cc . $digits;
        }

        return $digits;
    }

    /** wa.me link (WhatsApp), or null if the phone is empty. */
    public static function whatsapp(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }
        $e = self::e164($phone);

        return $e ? 'https://wa.me/' . $e : null;
    }

    /** tel: link, or null. */
    public static function tel(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }
        $e = self::e164($phone);

        return $e ? 'tel:+' . $e : null;
    }
}
