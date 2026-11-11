<?php

namespace App\Enums;

enum SchoolType: string
{
    case Kindergarten = 'kindergarten';
    case Primary = 'primary';
    case Both = 'both';

    public function label(): string
    {
        return match ($this) {
            self::Kindergarten => 'Jardín',
            self::Primary => 'Básica primaria',
            self::Both => 'Jardín y primaria',
        };
    }
}