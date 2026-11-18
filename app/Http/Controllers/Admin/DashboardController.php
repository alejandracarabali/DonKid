<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // Inicio de coordinación: cifras del colegio y avance de bitácoras de hoy.
    // No hace falta filtrar por school_id: BelongsToSchool ya lo hace en cada consulta.
    public function __invoke(): View
    {
        // Por grupo: total de estudiantes y cuántos tienen bitácora de hoy.
        // Misma regla que "Mi grupo" de la docente, para que las cifras coincidan.
        $classrooms = Classroom::query()
            ->withCount([
                'children',
                'children as sent_count' => fn ($q) => $q->whereHas(
                    'dailyReports',
                    fn ($r) => $r->whereDate('report_date', today())
                ),
            ])
            ->orderBy('name')
            ->get();

        return view('admin.dashboard', [
            'stats' => [
                ['label' => 'Grupos', 'value' => $classrooms->count(), 'route' => 'admin.classrooms.index'],
                ['label' => 'Estudiantes', 'value' => Child::count(), 'route' => 'admin.children.index'],
                ['label' => 'Familias', 'value' => User::where('role', Role::Guardian)->count(), 'route' => 'admin.guardians.index'],
                ['label' => 'Docentes', 'value' => User::where('role', Role::Educator)->count(), 'route' => 'admin.educators.index'],
            ],
            'classrooms' => $classrooms,
            'sent' => $classrooms->sum('sent_count'),
            'total' => $classrooms->sum('children_count'),
        ]);
    }
}