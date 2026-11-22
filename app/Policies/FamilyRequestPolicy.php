<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\FamilyRequest;
use App\Models\User;

class FamilyRequestPolicy
{
    public function view(User $user, FamilyRequest $request): bool
    {
        return match ($user->role) {
            Role::Admin => true,
            Role::Educator => $request->child?->classroom?->isLedBy($user) ?? false,
            Role::Guardian => $request->guardian_id === $user->id,
            default => false,
        };
    }

    // Marcar como vista o confirmar: el director de grupo o coordinación.
    public function handle(User $user, FamilyRequest $request): bool
    {
        return in_array($user->role, [Role::Admin, Role::Educator], true) && $this->view($user, $request);
    }
}
