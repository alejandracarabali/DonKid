<?php

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * RNF-11: horario en que los docentes reciben mensajes de las familias.
 * Un mensaje enviado a las 9:30 p. m. le aparece al docente a las 6:00 a. m. del día siguiente.
 */
class MessagingHours
{
    // Momento en que el docente verá un mensaje enviado ahora (se devuelve en UTC, como se guarda).
    public static function deliveryTime(?Carbon $now = null): Carbon
    {
        $local = ($now ?? now())->copy()->setTimezone(config('messaging.timezone'));
        $opens = (int) config('messaging.opens_at');
        $closes = (int) config('messaging.closes_at');

        if ($local->hour < $opens) {
            $local->setTime($opens, 0);           // de madrugada: hoy a las 6:00
        } elseif ($local->hour >= $closes) {
            $local->addDay()->setTime($opens, 0); // de noche: mañana a las 6:00
        }

        return $local->utc();
    }

    public static function isOpen(?Carbon $now = null): bool
    {
        return self::deliveryTime($now)->equalTo(($now ?? now())->copy()->utc());
    }

    // "6:00 a. m." para mostrarlo a la familia.
    public static function opensAtLabel(): string
    {
        return Carbon::createFromTime((int) config('messaging.opens_at'))->format('g:i').' a. m.';
    }
}
