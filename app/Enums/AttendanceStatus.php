<?php

namespace App\Enums;

enum AttendanceStatus: string
{
    case Present = 'present';
    case Late = 'late';
    case Absent = 'absent';

    // Texto para la interfaz. En la base de datos se guarda el valor en inglés.
    public function label(): string
    {
        return match ($this) {
            self::Present => 'Presente',
            self::Late => 'Tarde',
            self::Absent => 'Ausente',
        };
    }
}