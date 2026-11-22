<?php

namespace App\Support;

use App\Enums\EnrollmentStatus;
use App\Enums\Role;
use App\Models\EnrollmentApplication;
use App\Models\User;

/**
 * Menú de cada rol, definido en UN solo lugar.
 * La barra inferior y el menú lateral solo dibujan lo que esta clase devuelve.
 */
class Navigation
{
    /**
     * @return list<array{label: string, url: string, current: bool, badge: int}>
     */
    public static function for(User $user): array
    {
        return match ($user->role) {
            Role::Admin => [
                self::item('Inicio', 'admin.dashboard', 'admin.dashboard'),
                self::item('Grupos', 'admin.classrooms.index', 'admin.classrooms.*'),
                self::item('Estudiantes', 'admin.children.index', 'admin.children.*'),
                self::item('Inscripciones', 'admin.enrollments.index', 'admin.enrollments.*',
                    EnrollmentApplication::where('status', EnrollmentStatus::Pending)->count()),
                self::item('Familias', 'admin.guardians.index', 'admin.guardians.*'),
                self::item('Docentes', 'admin.educators.index', 'admin.educators.*'),
                self::item('Actividades', 'educator.activities.index', 'educator.activities.*'),
                self::item('Nuevo aviso', 'educator.announcements.create', 'educator.announcements.*'),
            ],
            Role::Educator => array_values(array_filter([
                // RF-54: sin bitácora en un colegio solo de primaria.
                self::offersDailyLog($user)
                    ? self::item('Bitácora', 'educator.reports.index', 'educator.reports.*')
                    : null,
                self::item('Grupos', 'educator.groups.index', 'educator.groups.*'),
                self::item('Actividades', 'educator.activities.index', 'educator.activities.*'),
                self::item('Avisos', 'educator.announcements.create', 'educator.announcements.*'),
                self::item('Perfil', 'profile.edit', 'profile.*'),
            ])),
            Role::Guardian => [
                self::item('Inicio', 'guardian.dashboard', 'guardian.dashboard'),
                self::item('Avisos', 'guardian.announcements.index', 'guardian.announcements.*',
                    $user->unreadNotifications()->count()),
                self::item('Actividades', 'guardian.activities.index', 'guardian.activities.*'),
            ],
        };
    }

    public static function offersDailyLog(User $user): bool
    {
        return $user->school?->offersDailyLog() ?? true;
    }

    /**
     * @return array{label: string, url: string, current: bool, badge: int}
     */
    private static function item(string $label, string $route, string $activePattern, int $badge = 0): array
    {
        return [
            'label' => $label,
            'url' => route($route),
            // routeIs acepta comodines: 'educator.reports.*' marca todas las páginas de bitácora.
            'current' => request()->routeIs($activePattern),
            'badge' => $badge,
        ];
    }
}