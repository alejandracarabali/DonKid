<?php

namespace App\Enums;

// Solicitudes que la familia le envía al colegio.
enum FamilyRequestType: string
{
    case Excuse = 'excuse';
    case Permission = 'permission';
    case Recommendation = 'recommendation';

    public function label(): string
    {
        return match ($this) {
            self::Excuse => 'Excusa',
            self::Permission => 'Permiso',
            self::Recommendation => 'Recomendación',
        };
    }

    public function hint(): string
    {
        return match ($this) {
            self::Excuse => 'No asistirá o llegará tarde',
            self::Permission => 'Recoge otra persona o salida anticipada',
            self::Recommendation => 'Indicaciones de salud o cuidado',
        };
    }
}
