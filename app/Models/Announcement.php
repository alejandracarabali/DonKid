<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Announcement extends Model
{
    use BelongsToSchool;

    // classroom_id, author_id y published_at los asigna el servidor.
    protected $fillable = ['title', 'body'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // Quiénes lo han leído (read_at en la tabla pivote).
    public function readers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'announcement_user')
            ->withPivot('read_at')
            ->withTimestamps();
    }

    // Pertenece a un colegio. Uso: $modelo->school->name
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    // El aviso queda en el colegio del grupo donde se publica.
    protected function inheritedSchoolId(): ?int
    {
        return Classroom::withoutGlobalScopes()->whereKey($this->classroom_id)->value('school_id');
    }
}