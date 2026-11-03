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

            // Pendiente: cuando exista la tabla de grupos por educadora,
            // se comprobará que el niño sea de SU grupo. Por ahora, no.
            Role::Educator => false,

            // La familia solo ve a SUS hijos (tabla child_guardian).
            Role::Guardian => $child->guardians()->whereKey($user->id)->exists(),
        };
    }
}