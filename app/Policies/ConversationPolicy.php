<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Conversation;
use App\Models\User;

class ConversationPolicy
{
    // ¿Puede ver (y responder) esta conversación?
    public function view(User $user, Conversation $conversation): bool
    {
        return match ($user->role) {
            Role::Admin => true, // coordinación puede revisar los mensajes de su colegio
            // RF-62: el director de grupo del niño.
            Role::Educator => $conversation->child?->classroom?->isLedBy($user) ?? false,
            Role::Guardian => $conversation->guardian_id === $user->id,
            default => false,
        };
    }
}
