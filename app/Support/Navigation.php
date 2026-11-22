<?php

namespace App\Support;

use App\Enums\EnrollmentStatus;
use App\Enums\Role;
use App\Models\EnrollmentApplication;
use App\Models\Message;
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
                self::item('Renovaciones', 'admin.renewals.index', 'admin.renewals.*'),
                self::item('Solicitudes', 'admin.requests.index', 'admin.requests.*'),
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
                // RF-27: Mensajes reemplaza a Perfil en la barra (Perfil sigue arriba, en el encabezado).
                self::item('Mensajes', 'educator.messages.index', 'educator.messages.*', self::unreadForStaff($user)),
            ])),
            Role::SuperAdmin => [
                self::item('Colegios', 'platform.schools.index', 'platform.schools.*'),
            ],
            Role::Guardian => [
                self::item('Inicio', 'guardian.dashboard', 'guardian.dashboard'),
                self::item('Avisos', 'guardian.announcements.index', 'guardian.announcements.*',
                    $user->unreadNotifications()->count()),
                self::item('Actividades', 'guardian.activities.index', 'guardian.activities.*'),
                self::item('Solicitudes', 'guardian.requests.index', 'guardian.requests.*'),
                self::item('Mensajes', 'guardian.messages.index', 'guardian.messages.*', self::unreadForGuardian($user)),
            ],
        };
    }

    // Mensajes de familias, ya en horario y sin leer, de los grupos que dirige.
    private static function unreadForStaff(User $user): int
    {
        return Message::query()->visible()->whereNull('messages.read_at')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->whereColumn('messages.sender_id', 'conversations.guardian_id')
            ->join('children', 'children.id', '=', 'conversations.child_id')
            ->whereIn('children.classroom_id', $user->ledClassrooms()->pluck('classrooms.id'))
            ->count();
    }

    private static function unreadForGuardian(User $user): int
    {
        return Message::query()->whereNull('messages.read_at')
            ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
            ->where('conversations.guardian_id', $user->id)
            ->where('messages.sender_id', '!=', $user->id)
            ->count();
    }

    public static function offersDailyLog(User $user): bool
    {
        if (! ($user->school?->offersDailyLog() ?? true)) {
            return false;
        }

        // RF-61: un docente solo por materia (tiene grupos, pero no dirige ninguno) no lleva bitácora.
        // Sin ningún grupo todavía, sí la ve: allí le explica que aún no tiene estudiantes.
        if ($user->role !== Role::Educator || ! $user->classrooms()->exists()) {
            return true;
        }

        return $user->ledClassrooms()->exists();
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