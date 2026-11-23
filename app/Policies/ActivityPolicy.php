<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Activity;
use App\Models\Classroom;
use App\Models\User;

class ActivityPolicy
{
    /**
     * Crear: Admin en cualquier grupo; educadora solo en un grupo suyo.
     * La familia nunca crea.
     */
    public function create(User $user, Classroom $classroom): bool
    {
        if ($user->role === Role::Admin) {
            return true;
        }

        return $user->role === Role::Educator
            && $user->classrooms()->whereKey($classroom->id)->exists();
    }

    /** Editar y borrar siguen la misma regla, según el grupo de la actividad. */
    public function update(User $user, Activity $activity): bool
    {
        return $this->create($user, $activity->classroom);
    }

    public function delete(User $user, Activity $activity): bool
    {
        return $this->create($user, $activity->classroom);
    }

    /**
     * Fotos (RF-32): coordinación y docentes del grupo, siempre.
     * Familias: solo si tienen un hijo en ese grupo CON autorización de imagen (RF-67).
     */
    public function viewPhotos(User $user, Activity $activity): bool
    {
        if ($user->role !== Role::Guardian) {
            return $this->view($user, $activity);
        }

        return $user->children()
            ->where('classroom_id', $activity->classroom_id)
            ->whereNotNull('image_consent_at')
            ->exists();
    }    

    /**
     * Ver: Admin todo; educadora los grupos suyos; familia solo los grupos
     * donde tiene hijos (privacidad por diseño).
     */
    public function view(User $user, Activity $activity): bool
    {
        return match ($user->role) {
            Role::Admin => true,
            Role::Educator => $user->classrooms()->whereKey($activity->classroom_id)->exists(),
            Role::Guardian => $user->children()->where('classroom_id', $activity->classroom_id)->exists(),
        };
    }
}