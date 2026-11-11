<?php

namespace App\Http\Requests\Concerns;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\Unique;

/**
 * Reglas de validación que respetan el colegio del usuario conectado.
 *
 * Las reglas exists/unique consultan la base de datos directamente,
 * SIN pasar por el filtro global de los modelos. Por eso hay que
 * agregar a mano la condición school_id.
 */
trait SchoolRules
{
    // El registro debe existir Y ser del colegio de quien envía el formulario.
    protected function existsInSchool(string $table): Exists
    {
        return Rule::exists($table, 'id')->where('school_id', $this->user()->school_id);
    }

    // El valor no se puede repetir DENTRO del colegio (otro colegio sí puede usarlo).
    protected function uniqueInSchool(string $table, string $column): Unique
    {
        return Rule::unique($table, $column)->where('school_id', $this->user()->school_id);
    }
}