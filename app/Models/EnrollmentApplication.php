<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\EnrollmentStatus;
use App\Enums\GradeLevel;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Solicitud de inscripción enviada por una familia desde el enlace público (RF-34).
 * BelongsToSchool: coordinación solo ve las solicitudes de su colegio.
 */
class EnrollmentApplication extends Model
{
    use BelongsToSchool;

    // status, reviewed_* y school_id NO son asignables: los pone el servidor.
    protected $fillable = [
        'child_first_name', 'child_last_name', 'child_birth_date', 'desired_level',
        'guardian_name', 'guardian_document_type', 'guardian_document_number',
        'guardian_relationship', 'guardian_email', 'guardian_phone', 'guardian_address',
    ];

    protected function casts(): array
    {
        return [
            'status' => EnrollmentStatus::class,
            'child_birth_date' => 'date',
            'desired_level' => GradeLevel::class,
            'guardian_document_type' => DocumentType::class,
            // Datos personales sensibles: cifrados con la APP_KEY.
            'guardian_document_number' => 'encrypted',
            'guardian_address' => 'encrypted',
            'reviewed_at' => 'datetime',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // Código corto para que la familia pueda referirse a su solicitud.
    public function reference(): string
    {
        return 'INS-'.str_pad((string) $this->id, 5, '0', STR_PAD_LEFT);
    }
}
