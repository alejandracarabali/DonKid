<?php

namespace App\Http\Controllers\Guardian;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityController extends Controller
{
    public function index(Request $request): View
    {
        $request->validate(['semana' => ['nullable', 'date']]);

        $day = $request->filled('semana')
            ? Carbon::parse($request->query('semana'))
            : now();
        $start = $day->copy()->startOfWeek();
        $end = $day->copy()->endOfWeek();

        // Privacidad por diseño: solo los grupos donde esta familia tiene hijos.
        $classroomIds = $request->user()->children()
            ->whereNotNull('children.classroom_id')
            ->pluck('children.classroom_id')
            ->unique();

        $activities = Activity::with('classroom')
            ->whereIn('classroom_id', $classroomIds)
            ->whereBetween('starts_at', [$start, $end])
            ->orderBy('starts_at')
            ->get()
            ->groupBy(fn (Activity $a) => $a->starts_at->toDateString());

        return view('guardian.activities.index', compact('activities', 'start', 'end'));
    }
}