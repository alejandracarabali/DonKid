<?php

namespace App\Http\Controllers\Educator;

use App\Actions\PublishDailyReport;
use App\Enums\MealStatus;
use App\Enums\Mood;
use App\Http\Controllers\Controller;
use App\Http\Requests\DailyReportRequest;
use App\Models\Child;
use App\Models\DailyReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class DailyReportController extends Controller
{
    // "Mi grupo": estudiantes del/los grupo(s) de la docente, con el avance del día.
    public function index(Request $request): View
    {
        $user = $request->user();

        $children = Child::query()
            ->when(
                $user->role->value !== 'admin',
                fn ($q) => $q->whereIn('classroom_id', $user->classrooms()->pluck('classrooms.id'))
            )
            ->with(['dailyReports' => fn ($q) => $q->whereDate('report_date', today())])
            ->orderBy('first_name')
            ->get()
            // Pendientes primero: false (sin reporte) va antes que true. Dentro de cada
            // grupo se conserva el orden alfabético porque el ordenamiento es estable.
            ->sortBy(fn (Child $child) => $child->dailyReports->isNotEmpty())
            ->values();

        $sent = $children->filter(fn (Child $child) => $child->dailyReports->isNotEmpty())->count();

        return view('educator.reports.index', [
            'children' => $children,
            'sent' => $sent,
            'total' => $children->count(),
        ]);
    }

    // Formulario de toques para un niño.
    public function create(Child $child): View
    {
        Gate::authorize('create', [DailyReport::class, $child]);

        $report = $child->dailyReports()->whereDate('report_date', today())->first();

        return view('educator.reports.create', [
            'child' => $child,
            'report' => $report,
            'moods' => Mood::cases(),
            'meals' => MealStatus::cases(),
        ]);
    }

    public function store(DailyReportRequest $request, Child $child, PublishDailyReport $publish): RedirectResponse
    {
        Gate::authorize('create', [DailyReport::class, $child]);

        $publish->handle($request->user(), $child, $request->validated());

        return redirect()->route('educator.reports.index')
            ->with('status', 'Listo, la familia de '.$child->first_name.' ya puede verlo.');
    }
}