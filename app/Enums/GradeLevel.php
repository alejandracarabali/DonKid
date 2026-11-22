<?php

namespace App\Enums;

// Nivel de un grupo. Define, entre otras cosas, si el grupo lleva bitácora diaria (RF-54).
enum GradeLevel: string
{
    case Parvulos = 'parvulos';
    case PreJardin = 'prejardin';
    case Jardin = 'jardin';
    case Transicion = 'transicion';
    case Primero = 'primero';
    case Segundo = 'segundo';
    case Tercero = 'tercero';
    case Cuarto = 'cuarto';
    case Quinto = 'quinto';

    public function label(): string
    {
        return match ($this) {
            self::Parvulos => 'Párvulos',
            self::PreJardin => 'Pre-jardín',
            self::Jardin => 'Jardín',
            self::Transicion => 'Transición',
            self::Primero => '1.°',
            self::Segundo => '2.°',
            self::Tercero => '3.°',
            self::Cuarto => '4.°',
            self::Quinto => '5.°',
        };
    }

    public function isPreschool(): bool
    {
        return in_array($this, self::preschool(), true);
    }

    // Nivel del año siguiente. 5.° no tiene: el estudiante termina primaria.
    public function next(): ?self
    {
        $all = self::cases();
        $i = array_search($this, $all, true);

        return $all[$i + 1] ?? null;
    }

    /** @return list<self> */
    public static function preschool(): array
    {
        return [self::Parvulos, self::PreJardin, self::Jardin, self::Transicion];
    }

    /** @return list<self> */
    public static function primary(): array
    {
        return [self::Primero, self::Segundo, self::Tercero, self::Cuarto, self::Quinto];
    }

    /**
     * Niveles que puede tener un grupo según el tipo de institución.
     *
     * @return list<self>
     */
    public static function forSchool(?SchoolType $type): array
    {
        return match ($type) {
            SchoolType::Kindergarten => self::preschool(),
            SchoolType::Primary => self::primary(),
            default => self::cases(),
        };
    }
}
