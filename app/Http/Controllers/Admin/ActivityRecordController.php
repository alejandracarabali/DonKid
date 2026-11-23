<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\Classroom;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * RF-32: coordinación ve en un solo lugar lo que cada grupo hizo, con la nota y las fotos.
 */
class ActivityRecordController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'grupo' => ['nullable', 'integer'],
            'desde' => ['nullable', 'date'],
            'hasta' => ['nullable', 'date', 'after_or_equal:desde'],
        ], ['hasta.after_or_equal' => 'La fecha "Hasta" debe ser igual o posterior a "Desde".']);

        // Por defecto, los últimos 30 días.
        $from = isset($filters['desde']) ? Carbon::parse($filters['desde'])->startOfDay() : today()->subDays(30);
        $to = isset($filters['hasta']) ? Carbon::parse($filters['hasta'])->endOfDay() : today()->endOfDay();

        // Solo las hechas, en el rango y (si se eligió) del grupo. Activity ya filtra por colegio.
        $query = Activity::whereNotNull('done_at')
            ->whereBetween('starts_at', [$from, $to])
            ->when($filters['grupo'] ?? null, fn ($q, $id) => $q->where('classroom_id', $id));

        // El resumen cuenta TODO lo filtrado, no solo la página actual. clone: cada conteo con su propia copia.
        $summary = [
            'done' => (clone $query)->count(),
            'photos' => (clone $query)->has('photos')->count(),
            'groups' => (clone $query)->distinct()->count('classroom_id'),
        ];

        $activities = $query->with(['classroom', 'doneBy', 'photos'])
            ->orderByDesc('starts_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.activity-records.index', [
            'activities' => $activities,
            'summary' => $summary,
            'classrooms' => Classroom::orderBy('name')->get(['id', 'name']),
            'from' => $from,
            'to' => $to,
        ]);
    }
}