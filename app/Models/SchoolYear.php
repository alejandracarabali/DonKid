<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * RF-02: año escolar de un colegio, con sus periodos.
 * Solo uno es el "actual" por colegio.
 */
class SchoolYear extends Model
{
    use BelongsToSchool;

    protected $fillable = ['year', 'starts_on', 'ends_on'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'is_current' => 'boolean'];
    }

    public function periods(): HasMany
    {
        return $this->hasMany(AcademicPeriod::class)->orderBy('number');
    }

    public static function current(): ?self
    {
        return static::where('is_current', true)->first();
    }

    // Lo marca como actual y desmarca los demás del mismo colegio.
    public function makeCurrent(): void
    {
        static::where('id', '!=', $this->id)->update(['is_current' => false]);
        $this->forceFill(['is_current' => true])->save();
    }

    // Periodo que corresponde a una fecha (por defecto, hoy).
    public function periodOn($date = null): ?AcademicPeriod
    {
        $date = ($date ?? today())->toDateString();

        return $this->periods->first(fn (AcademicPeriod $p) => $p->starts_on->toDateString() <= $date && $p->ends_on->toDateString() >= $date);
    }
}
