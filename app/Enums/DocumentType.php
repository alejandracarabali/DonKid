<?php

namespace App\Enums;

// Tipo de documento de identidad del acudiente.
enum DocumentType: string
{
    case Cc = 'cc';
    case Ce = 'ce';
    case Ppt = 'ppt';
    case Passport = 'pa';

    public function label(): string
    {
        return match ($this) {
            self::Cc => 'Cédula de ciudadanía',
            self::Ce => 'Cédula de extranjería',
            self::Ppt => 'Permiso por protección temporal (PPT)',
            self::Passport => 'Pasaporte',
        };
    }
}
