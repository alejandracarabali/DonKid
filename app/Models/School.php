<?php

namespace App\Models;

use App\Enums\SchoolType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperSchool
 */
class School extends Model
{
    use HasFactory;

    // suspended_at no es asignable: solo cambia con suspend() y reactivate().
    protected $fillable = ['name', 'slug', 'type', 'plan'];

    protected function casts(): array
    {
        return ['type' => SchoolType::class, 'renewal_year' => 'integer', 'suspended_at' => 'datetime'];
    }

    // RF-56: un colegio suspendido conserva sus datos, pero nadie de él puede ingresar.
    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    public function suspend(): void
    {
        $this->forceFill(['suspended_at' => now()])->save();
    }

    public function reactivate(): void
    {
        $this->forceFill(['suspended_at' => null])->save();
    }

    // Cada colegio nuevo arranca con materias, dimensiones y escala de desempeños por defecto.
    protected static function booted(): void
    {
        static::created(fn (School $school) => \App\Support\AcademicDefaults::seed($school));
    }

    // RF-36: ¿hay renovación de matrícula abierta?
    public function renewalOpen(): bool
    {
        return $this->renewal_year !== null;
    }

    // RF-54: un colegio solo de primaria no tiene bitácora en ningún grupo.
    public function offersDailyLog(): bool
    {
        return $this->type !== SchoolType::Primary;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function classrooms(): HasMany
    {
        return $this->hasMany(Classroom::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Child::class);
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }
}