<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\Child;
use App\Models\DailyReport;
use App\Models\User;

class DailyReportPolicy
{
    // ¿Puede este usuario ver ESTE reporte?
    public function view(User $user, DailyReport $report): bool
    {
        return match ($user->role) {
            Role::Admin => true,

            // La educadora ve los reportes de niños de SUS grupos.
            Role::Educator => $user->can('view', $report->child),

            // La familia solo ve reportes ya publicados de SUS hijos.
            Role::Guardian => $report->published_at !== null
                && $user->can('view', $report->child),
        };
    }

    // ¿Puede este usuario registrar un reporte para ESTE niño?
    public function create(User $user, Child $child): bool
    {
        return match ($user->role) {
            Role::Admin => true,
            Role::Educator => $user->can('view', $child),
            Role::Guardian => false,
        };
    }

    // ¿Puede publicar (hacer visible a la familia) este reporte?
    public function publish(User $user, DailyReport $report): bool
    {
        return $this->create($user, $report->child);
    }
}