<?php

namespace App\Models;

use App\Enums\GradeLevel;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Renovación de matrícula de un niño para un año (RF-36).
 */
class EnrollmentRenewal extends Model
{
    use BelongsToSchool;

    // Solo lo que llena la familia. Año, colegio, acudiente y prueba de autorización los pone el servidor.
    protected $fillable = ['next_level', 'guardian_phone', 'guardian_address'];

    protected function casts(): array
    {
        return [
            'next_level' => GradeLevel::class,
            'guardian_address' => 'encrypted',
            'data_consent_at' => 'datetime',
            'health_consent_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class)->withoutGlobalScope('active');
    }

    // El niño hereda el colegio (BelongsToSchool).
    protected function inheritedSchoolId(): ?int
    {
        return Child::withoutGlobalScopes()->whereKey($this->child_id)->value('school_id');
    }
}
