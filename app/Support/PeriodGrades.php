<?php

namespace App\Support;

use Illuminate\Support\Collection;

/**
 * RF-07: nota del periodo = promedio de las notas del estudiante, con un decimal.
 */
class PeriodGrades
{
    /**
     * @param  Collection<int, \App\Models\Assessment>  $assessments  con sus scores cargados
     * @return array<int, float>  child_id => promedio (solo quien tiene al menos una nota)
     */
    public static function averages(Collection $assessments): array
    {
        return $assessments->flatMap->scores
            ->groupBy('child_id')
            ->map(fn (Collection $scores) => self::round($scores->avg('score')))
            ->all();
    }

    // Redondeo escolar a un decimal: 2.95 → 3.0. Se suma un margen mínimo para
    // que errores de decimales (2.9499999…) no bajen la nota.
    public static function round(float $value): float
    {
        return round($value + 1e-9, 1);
    }
}
