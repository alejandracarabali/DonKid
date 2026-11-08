<?php

namespace App\Http\Controllers\Educator;

use App\Http\Controllers\Controller;
use App\Http\Requests\ActivityRequest;
use App\Models\Activity;
use App\Models\Classroom;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ActivityController extends Controller
{
    /** Grupos que el usuario puede gestionar (admin: todos; educadora: los suyos). */
    private function classroomsFor(Request $request)
    {
        $user = $request->user();

        return $user->role->value === 'admin'
            ? Classroom::orderBy('name')->get()
            : $user->classrooms()->orderBy('name')->get();
    }

    public function index(Request $request): View
    {
        $classrooms = $this->classroomsFor($request);

        // Semana a mostrar: ?semana=2026-11-02 (cualquier día de esa semana).
        $day = $request->filled('semana')
            ? Carbon::parse($request->query('semana'))
            : now();
        $start = $day->copy()->startOfWeek();
        $end = $day->copy()->endOfWeek();

        // Filtro opcional por grupo, limitado a los grupos permitidos.
        $selected = $classrooms->firstWhere('id', $request->integer('grupo'));

        $activities = Activity::with('classroom')
            ->whereIn('classroom_id', $selected ? [$selected->id] : $classrooms->pluck('id'))
            ->whereBetween('starts_at', [$start, $end])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Activity $a) => $a->starts_at->toDateString());

        return view('educator.activities.index', [
            'classrooms' => $classrooms,
            'selected' => $selected,
            'activities' => $activities,
            'start' => $start,
            'end' => $end,
        ]);
    }

    public function create(Request $request): View
    {
        return view('educator.activities.create', [
            'classrooms' => $this->classroomsFor($request),
        ]);
    }

    public function store(ActivityRequest $request): RedirectResponse
    {
        $classroom = Classroom::findOrFail($request->integer('classroom_id'));

        Gate::authorize('create', [Activity::class, $classroom]);

        $activity = new Activity($request->safe()->only(['title', 'description', 'starts_at', 'ends_at']));
        $activity->classroom_id = $classroom->id;
        $activity->author_id = $request->user()->id;
        $activity->save();

        return redirect()->route('educator.activities.index')
            ->with('status', 'Actividad guardada.');
    }

    public function edit(Activity $activity): View
    {
        Gate::authorize('update', $activity);

        return view('educator.activities.edit', compact('activity'));
    }

    public function update(ActivityRequest $request, Activity $activity): RedirectResponse
    {
        Gate::authorize('update', $activity);

        // El grupo no se cambia al editar: la actividad sigue en su grupo.
        $activity->update($request->safe()->only(['title', 'description', 'starts_at', 'ends_at']));

        return redirect()->route('educator.activities.index')
            ->with('status', 'Actividad actualizada.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        Gate::authorize('delete', $activity);

        $activity->delete();

        return redirect()->route('educator.activities.index')
            ->with('status', 'Actividad eliminada.');
    }
}