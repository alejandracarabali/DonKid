<?php

namespace App\Enums;

enum MealStatus: string
{
    case All = 'all';
    case Most = 'most';
    case Little = 'little';
    case None = 'none';

    // Texto corto para mostrar en pantalla (UX Writing).
    public function label(): string
    {
        return match ($this) {
            self::All => 'Comió todo',
            self::Most => 'Comió casi todo',
            self::Little => 'Comió poco',
            self::None => 'No comió',
        };
    }

    // Ícono para el toque rápido.
    public function icon(): string
    {
        return match ($this) {
            self::All => '😋',
            self::Most => '🙂',
            self::Little => '😐',
            self::None => '🚫',
        };
    }
}