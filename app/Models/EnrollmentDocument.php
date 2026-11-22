<?php

namespace App\Models;

use App\Enums\EnrollmentDocumentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Documento adjunto a una solicitud de inscripción.
 * El archivo está en el disco privado: nunca tiene una URL pública.
 */
class EnrollmentDocument extends Model
{
    protected $fillable = ['type', 'path', 'original_name', 'mime_type', 'size'];

    protected function casts(): array
    {
        return ['type' => EnrollmentDocumentType::class];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(EnrollmentApplication::class, 'enrollment_application_id');
    }
}
