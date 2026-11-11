<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Announcement;
use App\Models\Classroom;
use App\Models\User;

class AnnouncementPolicy
{
    // ¿Puede este usuario publicar en ESTE grupo? (null = aviso general)
    public function create(User $user, ?Classroom $classroom): bool
    {
        return match ($user->role) {
            Role::Admin => true,
            Role::Educator => $classroom !== null
                && $user->classrooms()->whereKey($classroom->id)->exists(),
            Role::Guardian => false,
        };
    }

    // ¿Puede leer este aviso?
    public function view(User $user, Announcement $announcement): bool
    {
        if ($announcement->published_at === null) {
            return $user->role === Role::Admin || $user->id === $announcement->author_id;
        }

        return match ($user->role) {
            Role::Admin => true,

            Role::Educator => $announcement->classroom_id === null
                || $user->classrooms()->whereKey($announcement->classroom_id)->exists(),

            // La familia ve avisos generales o de grupos donde tiene hijos.
            Role::Guardian => $announcement->classroom_id === null
                || $user->children()->where('classroom_id', $announcement->classroom_id)->exists(),
        };
    }
}