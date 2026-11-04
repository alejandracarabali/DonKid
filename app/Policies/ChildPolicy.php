<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Child;
use App\Models\User;

class ChildPolicy
{
    // ¿Puede este usuario ver a ESTE niño?
    public function view(User $user, Child $child): bool
    {
        return match ($user->role) {
            // La coordinación ve a todos.
            Role::Admin => true,

            // La educadora solo ve a los niños de SUS grupos.
            Role::Educator => $child->classroom_id !== null
                && $user->classrooms()->whereKey($child->classroom_id)->exists(),

            // La familia solo ve a SUS hijos (tabla child_guardian).
            Role::Guardian => $child->guardians()->whereKey($user->id)->exists(),
        };
    }
}