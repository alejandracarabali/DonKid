<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Aísla los datos por colegio (multi-tenant con una sola base de datos).
 *
 * 1. Filtro global: toda consulta del modelo trae SOLO los registros
 *    del colegio del usuario conectado.
 * 2. Al crear sin school_id, el colegio se toma, en este orden:
 *    a) del "padre" del registro (por ejemplo, el grupo de un niño);
 *    b) del usuario conectado.
 *
 * Sin usuario conectado (consola, colas, seeders, login) no se filtra.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope('school', function (Builder $query) {
            if (static::hasSchoolUser()) {
                // qualifyColumn => "children.school_id": evita ambigüedad en los JOIN.
                $query->where($query->qualifyColumn('school_id'), auth()->guard()->user()->school_id);
            }
        });

        static::creating(function (Model $model) {
            if ($model->school_id === null) {
                $model->school_id = $model->inheritedSchoolId()
                    ?? (static::hasSchoolUser() ? auth()->guard()->user()->school_id : null);
            }
        });
    }

    /**
     * Colegio heredado del "padre" del registro. Por defecto no hay padre;
     * los modelos que lo tienen (Child, Announcement) sobrescriben este método.
     */
    protected function inheritedSchoolId(): ?int
    {
        return null;
    }

    /**
     * hasUser() solo pregunta si el usuario YA está cargado; no lo carga.
     * Con check() habría un bucle infinito en el modelo User:
     * cargar al usuario dispara el filtro, y el filtro pide cargar al usuario.
     */
    protected static function hasSchoolUser(): bool
    {
        return auth()->guard()->hasUser();
    }
}