<?php

use App\Libraries\Tz;

if (! function_exists('dt')) {
    /**
     * Format a UTC-stored datetime string in the current user's/condominio's
     * timezone. Convenience wrapper over App\Libraries\Tz for views.
     */
    function dt(?string $utc, string $fmt = 'd/m/Y H:i'): string
    {
        return Tz::disp($utc, $fmt);
    }
}
