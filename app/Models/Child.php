<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @mixin IdeHelperChild
 */
class Child extends Model
{
    use HasFactory, BelongsToSchool;

    protected $fillable = [
        'classroom_id', 'first_name', 'last_name', 'birth_date', 'allergies',
    ];

    // Laravel convierte el texto de la BD en un objeto fecha (Carbon).
    protected function casts(): array
    {
        return ['birth_date' => 'date'];
    }

    // Un niño pertenece a un grupo.
    // Uso: $child->classroom->name
    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    // Un niño tiene varios acudientes.
    public function guardians(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'child_guardian')
            ->withPivot('relationship')
            ->withTimestamps();
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

    // Pertenece a un colegio. Uso: $modelo->school->name
    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    
    // El niño queda SIEMPRE en el colegio de su grupo.
    // withoutGlobalScopes(): se lee el colegio real del grupo, sin el filtro del usuario.
    protected function inheritedSchoolId(): ?int
    {
        return Classroom::withoutGlobalScopes()->whereKey($this->classroom_id)->value('school_id');
    }
}