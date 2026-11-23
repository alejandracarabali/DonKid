<?php

namespace App\Models;

use App\Enums\FamilyRequestStatus;
use App\Enums\FamilyRequestType;
use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Solicitud de la familia: excusa, permiso o recomendación.
 * La atiende el director de grupo del niño; coordinación las ve todas.
 */
class FamilyRequest extends Model
{
    use BelongsToSchool;

    public const EXCUSE_REASONS = ['Enfermedad', 'Cita médica', 'Viaje o familia', 'Otro'];
    public const PERMISSION_KINDS = ['other_person' => 'Recoge otra persona', 'early_exit' => 'Salida anticipada'];
    public const RELATIONSHIPS = ['Tío/a', 'Abuelo/a', 'Vecino/a', 'Amigo/a', 'Otro'];

    // type, status, código y quién la atendió los pone el servidor.
    protected $fillable = ['date', 'time', 'reason', 'note', 'pickup_name', 'pickup_relationship', 'pickup_phone'];

    protected function casts(): array
    {
        return [
            'type' => FamilyRequestType::class,
            'status' => FamilyRequestStatus::class,
            'date' => 'date',
            'note' => 'encrypted',
            'handled_at' => 'datetime',
        ];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(Child::class)->withoutGlobalScope('active');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guardian_id');
    }

    public function isPickupByOther(): bool
    {
        return $this->type === FamilyRequestType::Permission && $this->reason === 'other_person';
    }

    // Resumen de una línea: "Excusa: Cita médica", "Recoge Carlos Ríos (tío/a)"...
    public function summary(): string
    {
        return match ($this->type) {
            FamilyRequestType::Excuse => 'Excusa: '.$this->reason,
            FamilyRequestType::Permission => $this->isPickupByOther()
                ? 'Recoge '.$this->pickup_name.' ('.mb_strtolower($this->pickup_relationship).')'
                : 'Salida anticipada',
            FamilyRequestType::Recommendation => 'Recomendación',
        };
    }

    // Tarjeta de solicitud: el "qué" en grande. "Cita médica", "Recoge otra persona"...
    public function headline(): string
    {
        return match ($this->type) {
            FamilyRequestType::Excuse => $this->reason,
            FamilyRequestType::Permission => self::PERMISSION_KINDS[$this->reason] ?? 'Permiso',
            FamilyRequestType::Recommendation => 'Para su cuidado',
        };
    }

    // Tarjeta de solicitud: el dato que el docente necesita de un vistazo (o null si no hay).
    public function highlight(): ?string
    {
        if ($this->type !== FamilyRequestType::Permission) {
            return null;
        }
        $at = $this->time ? ' a las '.substr($this->time, 0, 5) : '';

        return $this->isPickupByOther()
            ? 'Recoge '.$this->pickup_name.' ('.mb_strtolower($this->pickup_relationship).')'.$at
            : ($at ? 'Sale'.$at : null);
    }

    protected function inheritedSchoolId(): ?int
    {
        return Child::withoutGlobalScopes()->whereKey($this->child_id)->value('school_id');
    }
}
