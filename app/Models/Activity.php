<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperActivity
 */
class Activity extends Model
{
    // Solo estos campos se asignan en masa. classroom_id y author_id
    // los pone el controlador, nunca el formulario (evita que alguien
    // cree actividades en un grupo ajeno manipulando la petición).
    protected $fillable = ['title', 'description', 'starts_at', 'ends_at'];

    /**
     * Una actividad no tiene school_id: pertenece al colegio de su grupo.
     * whereHas('classroom') agrega "y que su grupo sea visible", y como
     * Classroom ya filtra por colegio, la actividad queda filtrada también.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('school', function (Builder $query) {
            if (auth()->guard()->hasUser()) {
                $query->whereHas('classroom');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // Registro (RF-32): la docente la marca como hecha, con nota y fotos.
    public function isDone(): bool
    {
        return $this->done_at !== null;
    }

    public function photos(): HasMany
    {
        return $this->hasMany(ActivityPhoto::class)->orderBy('id');
    }

    public function doneBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'done_by');
    }    
}