<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * RF-02: coordinación crea el año escolar y ajusta las fechas de sus periodos.
 */
class SchoolYearController extends Controller
{
    public function index(): View
    {
        return view('admin.school-years.index', [
            'years' => SchoolYear::with('periods')->orderByDesc('year')->get(),
        ]);
    }

    public function create(): View
    {
        $year = now()->year + 1;

        // Sugerencia para calendario A: finales de enero a finales de noviembre.
        return view('admin.school-years.create', [
            'year' => $year,
            'startsOn' => Carbon::create($year, 1, 26)->toDateString(),
            'endsOn' => Carbon::create($year, 11, 27)->toDateString(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'year' => ['required', 'integer', 'between:2020,2100',
                Rule::unique('school_years')->where('school_id', $request->user()->school_id)],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after:starts_on'],
        ], [
            'year.unique' => 'Ya existe ese año escolar.',
            'ends_on.after' => 'El año debe terminar después de empezar.',
        ]);

        $schoolYear = DB::transaction(function () use ($data) {
            $schoolYear = SchoolYear::create($data);
            $this->splitIntoPeriods($schoolYear);

            // El primero que se crea queda como año actual.
            if (! SchoolYear::where('is_current', true)->exists()) {
                $schoolYear->makeCurrent();
            }

            return $schoolYear;
        });

        return redirect()->route('admin.school-years.edit', $schoolYear)
            ->with('status', "Listo, se creó el año {$schoolYear->year} con ".config('academic.periods').' periodos. Ajusta sus fechas si hace falta.');
    }

    public function edit(SchoolYear $schoolYear): View
    {
        return view('admin.school-years.edit', ['schoolYear' => $schoolYear->load('periods')]);
    }

    // Guarda las fechas de los periodos.
    public function update(Request $request, SchoolYear $schoolYear): RedirectResponse
    {
        $schoolYear->load('periods');
        $rules = [];
        foreach ($schoolYear->periods as $period) {
            $n = $period->number;
            $rules["periods.$n.starts_on"] = ['required', 'date'];
            $rules["periods.$n.ends_on"] = ['required', 'date', "after:periods.$n.starts_on"];
            $rules["periods.$n.grades_due_on"] = ['required', 'date', "after_or_equal:periods.$n.starts_on"];
        }
        $data = $request->validate($rules, [
            'periods.*.ends_on.after' => 'El periodo debe terminar después de empezar.',
            'periods.*.grades_due_on.after_or_equal' => 'La fecha límite de notas no puede ser antes de que empiece el periodo.',
        ]);

        $this->ensureSequential($schoolYear, $data['periods']);

        DB::transaction(function () use ($schoolYear, $data) {
            foreach ($schoolYear->periods as $period) {
                $period->update($data['periods'][$period->number]);
            }
        });

        return redirect()->route('admin.school-years.index')->with('status', 'Listo, las fechas de los periodos quedaron guardadas.');
    }

    public function makeCurrent(SchoolYear $schoolYear): RedirectResponse
    {
        $schoolYear->makeCurrent();

        return back()->with('status', "El año {$schoolYear->year} es ahora el año actual.");
    }

    // Reparte el año en N periodos seguidos de igual duración (se pueden ajustar después).
    private function splitIntoPeriods(SchoolYear $schoolYear): void
    {
        $count = (int) config('academic.periods');
        $totalDays = $schoolYear->starts_on->diffInDays($schoolYear->ends_on) + 1;
        $start = $schoolYear->starts_on->copy();

        for ($n = 1; $n <= $count; $n++) {
            $end = $n === $count
                ? $schoolYear->ends_on->copy()
                : $schoolYear->starts_on->copy()->addDays((int) floor($totalDays * $n / $count) - 1);

            $schoolYear->periods()->create([
                'number' => $n,
                'starts_on' => $start,
                'ends_on' => $end,
                'grades_due_on' => $end,
            ]);

            $start = $end->copy()->addDay();
        }
    }

    // Los periodos van en orden, sin cruzarse, y dentro del año escolar.
    private function ensureSequential(SchoolYear $schoolYear, array $periods): void
    {
        $errors = [];
        $previousEnd = null;
        ksort($periods);

        foreach ($periods as $n => $p) {
            $starts = Carbon::parse($p['starts_on']);
            $ends = Carbon::parse($p['ends_on']);

            if ($starts->lt($schoolYear->starts_on) || $ends->gt($schoolYear->ends_on)) {
                $errors["periods.$n.starts_on"] = 'El periodo debe estar dentro del año escolar ('
                    .$schoolYear->starts_on->format('d/m/Y').' a '.$schoolYear->ends_on->format('d/m/Y').').';
            }
            if ($previousEnd && $starts->lte($previousEnd)) {
                $errors["periods.$n.starts_on"] = 'Debe empezar después de que termine el periodo anterior.';
            }
            $previousEnd = $ends;
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }
    }
}
