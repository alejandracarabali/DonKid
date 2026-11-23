<?php

namespace App\Enums;

// RF-04: en primaria se evalúan materias (con nota); en preescolar, dimensiones del desarrollo (con texto).
enum SubjectKind: string
{
    case Subject = 'subject';
    case Dimension = 'dimension';

    public function label(): string
    {
        return match ($this) {
            self::Subject => 'Materia (primaria)',
            self::Dimension => 'Dimensión (preescolar)',
        };
    }

    // Qué se evalúa en cada tipo de colegio.
    /** @return list<self> */
    public static function forSchool(?SchoolType $type): array
    {
        return match ($type) {
            SchoolType::Kindergarten => [self::Dimension],
            SchoolType::Primary => [self::Subject],
            default => [self::Subject, self::Dimension],
        };
    }
}
