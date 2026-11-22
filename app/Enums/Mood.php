<?php

namespace App\Enums;

enum Mood: string
{
    // El valor (texto entre comillas) es lo que se guarda en la BD: nunca se cambia.
    case Happy = 'happy';
    case Calm = 'calm';
    case Tired = 'tired';
    case Sad = 'sad';
    case Fussy = 'fussy';
    case Angry = 'angry';

    // Texto corto para mostrar en pantalla (UX Writing).
    // La etiqueta sí se puede cambiar: no se guarda en la BD.
    public function label(): string
    {
        return match ($this) {
            self::Happy => 'Feliz',
            self::Calm => 'Tranquilo',
            self::Tired => 'Cansado',
            self::Sad => 'Triste',
            self::Fussy => 'Emocionado',
            self::Angry => 'Enojado',
        };
    }

    // Ruta de la ilustración de DonKid para este ánimo (public/images/emociones).
    public function image(): string
    {
        $file = match ($this) {
            self::Happy => 'feliz',
            self::Calm => 'relajado',
            self::Tired => 'cansado',
            self::Sad => 'triste',
            self::Fussy => 'emocionado',
            self::Angry => 'enojado',
        };

        return asset("images/emociones/{$file}.webp");
    }
}