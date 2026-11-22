<?php

namespace App\Enums;

enum FamilyRequestStatus: string
{
    case Sent = 'sent';
    case Seen = 'seen';
    case Confirmed = 'confirmed';

    public function label(): string
    {
        return match ($this) {
            self::Sent => 'Enviada',
            self::Seen => '✓ Vista',
            self::Confirmed => '✓ Confirmada',
        };
    }
}
