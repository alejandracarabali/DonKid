<?php

namespace App\Models;

use App\Models\Concerns\BelongsToSchool;
use Illuminate\Database\Eloquent\Model;

/**
 * RF-05: desempeño (Bajo, Básico, Alto, Superior) y su rango de nota.
 */
class PerformanceLevel extends Model
{
    use BelongsToSchool;

    protected $fillable = ['name', 'min', 'max', 'position'];

    protected function casts(): array
    {
        return ['min' => 'float', 'max' => 'float'];
    }

    // Desempeño que corresponde a una nota (ej. 4.2 → Alto). Compara en décimas para evitar errores de decimales.
    public static function for(float $grade): ?self
    {
        return static::match(static::orderBy('position')->get(), $grade);
    }

    // Igual que for(), pero con la escala ya cargada: sirve para listas sin repetir la consulta.
    /** @param \Illuminate\Support\Collection<int, self> $levels */
    public static function match($levels, float $grade): ?self
    {
        $tenths = (int) round($grade * 10);

        return $levels->first(fn (self $l) => (int) round($l->min * 10) <= $tenths && $tenths <= (int) round($l->max * 10));
    }
}
