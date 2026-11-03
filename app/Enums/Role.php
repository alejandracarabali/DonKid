<?php

namespace App\Enums;

// Enum "backed": cada caso tiene un valor string que se guarda en la BD.
enum Role: string
{
    case Admin = 'admin';
    case Educator = 'educator';
    case Guardian = 'guardian';

    // Texto para mostrar al usuario. Un solo lugar = UX Writing consistente.
    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Coordinación',
            self::Educator => 'Educadora',
            self::Guardian => 'Familia',
        };
    }

    // Cada rol tiene su "casa": la ruta a la que llega tras iniciar sesión.
    public function homeRoute(): string
    {
        return match ($this) {
            self::Admin => 'admin.dashboard',
            self::Educator => 'educator.dashboard',
            self::Guardian => 'guardian.dashboard',
        };
    }
}