<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Foto de una actividad hecha. El archivo vive en el disco privado 'local'.
 */
class ActivityPhoto extends Model
{
    protected $fillable = ['path', 'uploaded_by'];

    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
}