<?php

namespace App\Models;

use App\Enums\SchoolType;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @mixin IdeHelperChild
 */
class Child extends Model
{
    use HasFactory, BelongsToSchool;

    // withdrawn_at y withdrawal_reason NO son asignables en bloque:
    // solo se cambian con withdraw() y reactivate().
    protected $fillable = [
        'classroom_id', 'first_name', 'last_name', 'birth_date', 'allergies',
    ];

    // Laravel convierte el texto de la BD en un objeto fecha (Carbon).
    protected function casts(): array
    {
        return ['birth_date' => 'date', 'withdrawn_at' => 'date'];
    }

    // RF-53: por defecto, toda consulta de niños trae solo a los activos.
    // Así un retirado desaparece de listas de grupo, bitácora, avisos y de la vista
    // de la familia sin tocar cada consulta. Para verlos: withoutGlobalScope('active').
    protected static function booted(): void
    {
        static::addGlobalScope('active', function (Builder $query) {
            $query->whereNull($query->qualifyColumn('withdrawn_at'));
        });
    }

    // Solo los retirados (usado por coordinación).
    public function scopeWithdrawn(Builder $query): Builder
    {
        return $query->withoutGlobalScope('active')->whereNotNull($query->qualifyColumn('withdrawn_at'));
    }

    // RF-54: el niño lleva bitácora si su grupo la lleva.
    // Sin grupo: solo en un colegio que sea únicamente jardín.
    public function hasDailyLog(): bool
    {
        return $this->classroom
            ? $this->classroom->hasDailyLog()
            : $this->school?->type === SchoolType::Kindergarten;
    }

    public function isWithdrawn(): bool
    {
        return $this->withdrawn_at !== null;
    }

    // Inhabilita al niño. Nada se borra: historial, bitácora y vínculos se conservan.
    public function withdraw(string $date, ?string $reason): void
    {
        $this->forceFill(['withdrawn_at' => $date, 'withdrawal_reason' => $reason])->save();
    }

    public function reactivate(): void
    {
        $this->forceFill(['withdrawn_at' => null, 'withdrawal_reason' => null])->save();
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

    // RF-38: ficha de salud (datos sensibles, en tabla aparte).
    public function healthRecord(): HasOne
    {
        return $this->hasOne(ChildHealthRecord::class);
    }

    // RF-36: renovaciones de matrícula, una por año.
    public function renewals(): HasMany
    {
        return $this->hasMany(EnrollmentRenewal::class);
    }

    public function dailyReports(): HasMany
    {
        return $this->hasMany(DailyReport::class);
    }

        // Uso: $child->attendances
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
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