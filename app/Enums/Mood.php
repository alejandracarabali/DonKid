<?php

namespace App\Enums;

enum Mood: string
{
    case Happy = 'happy';
    case Calm = 'calm';
    case Tired = 'tired';
    case Sad = 'sad';
    case Fussy = 'fussy';

    // Texto corto para mostrar en pantalla (UX Writing).
    public function label(): string
    {
        return match ($this) {
            self::Happy => 'Feliz',
            self::Calm => 'Tranquilo',
            self::Tired => 'Cansado',
            self::Sad => 'Triste',
            self::Fussy => 'Inquieto',
        };
    }

    // Ícono para el toque rápido.
    public function icon(): string
    {
        return match ($this) {
            self::Happy => '😄',
            self::Calm => '😊',
            self::Tired => '😴',
            self::Sad => '😢',
            self::Fussy => '😣',
        };
    }
}