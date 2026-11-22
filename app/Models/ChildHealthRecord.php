<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ficha de salud de un niño (RF-38). Una por niño.
 * La ven coordinación y las docentes de su grupo (ChildPolicy::view).
 */
class ChildHealthRecord extends Model
{
    protected $fillable = [
        'eps', 'conditions', 'medications',
        'emergency_contact_name', 'emergency_contact_phone',
    ];

    // 'encrypted': Laravel cifra al guardar y descifra al leer, con la APP_KEY.
    // En la base de datos solo queda texto ilegible.
    protected function casts(): array
    {
        return [
            'conditions' => 'encrypted',
            'medications' => 'encrypted',
        ];
    }

    // La ficha sigue ligada a su niño aunque se retire (RF-53).
    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class)->withoutGlobalScope('active');
    }
}
