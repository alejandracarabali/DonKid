<?php

namespace App\Support;

use App\Enums\SubjectKind;
use App\Models\School;
use Illuminate\Support\Facades\DB;

/**
 * Valores iniciales de cada colegio: materias, dimensiones y escala de desempeños.
 * Coordinación los ajusta después en la app.
 */
class AcademicDefaults
{
    // Áreas obligatorias y fundamentales de la educación básica (Ley 115 de 1994, art. 23).
    public const SUBJECTS = [
        'Matemáticas',
        'Lengua castellana',
        'Inglés',
        'Ciencias naturales y educación ambiental',
        'Ciencias sociales',
        'Educación artística y cultural',
        'Educación física, recreación y deportes',
        'Educación ética y en valores humanos',
        'Educación religiosa',
        'Tecnología e informática',
    ];

    // Dimensiones del desarrollo (lineamientos curriculares de preescolar del MEN).
    public const DIMENSIONS = [
        'Cognitiva',
        'Comunicativa',
        'Corporal',
        'Socioafectiva',
        'Estética',
        'Ética',
        'Espiritual',
    ];

    // Escala de desempeños (Decreto 1290 de 2009) con los rangos más comunes.
    public const PERFORMANCE = [
        ['Bajo', 1.0, 2.9],
        ['Básico', 3.0, 3.9],
        ['Alto', 4.0, 4.5],
        ['Superior', 4.6, 5.0],
    ];

    public static function seed(School $school): void
    {
        $now = now();
        $rows = [];
        $kinds = SubjectKind::forSchool($school->type);

        foreach ([SubjectKind::Subject->value => self::SUBJECTS, SubjectKind::Dimension->value => self::DIMENSIONS] as $kind => $names) {
            if (! in_array(SubjectKind::from($kind), $kinds, true)) {
                continue;
            }
            foreach ($names as $i => $name) {
                $rows[] = ['school_id' => $school->id, 'kind' => $kind, 'name' => $name, 'position' => $i + 1,
                    'active' => true, 'created_at' => $now, 'updated_at' => $now];
            }
        }
        DB::table('subjects')->insertOrIgnore($rows);

        $levels = [];
        foreach (self::PERFORMANCE as $i => [$name, $min, $max]) {
            $levels[] = ['school_id' => $school->id, 'name' => $name, 'min' => $min, 'max' => $max,
                'position' => $i + 1, 'created_at' => $now, 'updated_at' => $now];
        }
        DB::table('performance_levels')->insertOrIgnore($levels);
    }
}
