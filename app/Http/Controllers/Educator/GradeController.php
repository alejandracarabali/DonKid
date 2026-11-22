<?php

namespace App\Http\Controllers\Educator;

use App\Enums\SubjectKind;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use App\Models\Assessment;
use App\Models\AssessmentScore;
use App\Models\Classroom;
use App\Models\DimensionReport;
use App\Models\PerformanceLevel;
use App\Models\SchoolYear;
use App\Models\Subject;
use App\Models\User;
use App\Support\PeriodGrades;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * RF-07: notas de primaria (varias actividades por periodo, se promedian).
 * RF-08: informe escrito por dimensión en preescolar.
 * Solo las registra el docente asignado a esa materia en ese grupo.
 */
class GradeController extends Controller
{
    // Materias (o dimensiones) que dicta el docente, agrupadas por grupo.
    public function index(Request $request): View
    {
        return view('educator.grades.index', ['classrooms' => self::assignmentsOf($request->user())]);
    }

    public function show(Request $request, Classroom $classroom, Subject $subject): View
    {
        $this->ensureTeaches($request->user(), $classroom, $subject);

        $year = SchoolYear::current()?->load('periods');
        $period = $year ? $this->resolvePeriod($year, $request->integer('periodo')) : null;
        $children = $classroom->children()->orderBy('first_name')->orderBy('last_name')->get();

        $data = compact('classroom', 'subject', 'year', 'period', 'children');

        if (! $period) {
            return view('educator.grades.show', $data);
        }

        if ($subject->kind === SubjectKind::Dimension) {
            $data['reports'] = DimensionReport::where('subject_id', $subject->id)
                ->where('academic_period_id', $period->id)
                ->whereIn('child_id', $children->pluck('id'))
                ->get()->mapWithKeys(fn ($r) => [$r->child_id => $r->body]);

            return view('educator.grades.show', $data);
        }

        $assessments = $this->assessments($classroom, $subject, $period)->with('scores')->get();

        return view('educator.grades.show', $data + [
            'assessments' => $assessments,
            // [assessment_id][child_id] => nota
            'scores' => $assessments->mapWithKeys(fn ($a) => [$a->id => $a->scores->pluck('score', 'child_id')]),
            'averages' => PeriodGrades::averages($assessments),
            'levels' => PerformanceLevel::orderBy('position')->get(),
        ]);
    }

    public function storeAssessment(Request $request, Classroom $classroom, Subject $subject): RedirectResponse
    {
        $period = $this->editablePeriod($request, $classroom, $subject);
        abort_unless($subject->kind === SubjectKind::Subject, 404);

        $data = $request->validate(
            ['title' => ['required', 'string', 'max:80']],
            ['title.required' => 'Escribe el nombre de la actividad (ej. Quiz de fracciones).']
        );

        Assessment::create($data + [
            'classroom_id' => $classroom->id,
            'subject_id' => $subject->id,
            'academic_period_id' => $period->id,
            'created_by' => $request->user()->id,
        ]);

        return $this->backToSheet($classroom, $subject, $period, 'Listo, se agregó la actividad. Ya puedes poner las notas.');
    }

    public function destroyAssessment(Request $request, Classroom $classroom, Subject $subject, Assessment $assessment): RedirectResponse
    {
        $period = $this->editablePeriod($request, $classroom, $subject);
        abort_unless($assessment->classroom_id === $classroom->id && $assessment->subject_id === $subject->id
            && $assessment->academic_period_id === $period->id, 404);

        $assessment->delete(); // sus notas se borran en cascada

        return $this->backToSheet($classroom, $subject, $period, "Se quitó «{$assessment->title}» y sus notas.");
    }

    public function saveScores(Request $request, Classroom $classroom, Subject $subject): RedirectResponse
    {
        $period = $this->editablePeriod($request, $classroom, $subject);
        abort_unless($subject->kind === SubjectKind::Subject, 404);

        $validated = $request->validate([
            'scores' => ['array'],
            'scores.*' => ['array'],
            'scores.*.*' => ['nullable', 'numeric', 'between:1,5', 'decimal:0,1'],
        ], [
            'scores.*.*.numeric' => 'Escribe la nota con números (ej. 4.5).',
            'scores.*.*.between' => 'Las notas van de 1.0 a 5.0.',
            'scores.*.*.decimal' => 'Usa máximo un decimal (ej. 3.5).',
        ]);

        // Solo actividades de esta planilla y estudiantes de este grupo; lo demás se ignora.
        $assessmentIds = $this->assessments($classroom, $subject, $period)->pluck('id');
        $childIds = $classroom->children()->pluck('id');

        DB::transaction(function () use ($validated, $assessmentIds, $childIds) {
            foreach ($assessmentIds as $assessmentId) {
                foreach ($childIds as $childId) {
                    $value = $validated['scores'][$assessmentId][$childId] ?? null;
                    $key = ['assessment_id' => $assessmentId, 'child_id' => $childId];

                    if ($value === null || $value === '') {
                        AssessmentScore::where($key)->delete(); // casilla vacía = sin nota
                    } else {
                        AssessmentScore::updateOrCreate($key, ['score' => $value]);
                    }
                }
            }
        });

        return $this->backToSheet($classroom, $subject, $period, 'Listo, las notas quedaron guardadas.');
    }

    public function saveReports(Request $request, Classroom $classroom, Subject $subject): RedirectResponse
    {
        $period = $this->editablePeriod($request, $classroom, $subject);
        abort_unless($subject->kind === SubjectKind::Dimension, 404);

        $validated = $request->validate([
            'reports' => ['array'],
            'reports.*' => ['nullable', 'string', 'max:2000'],
        ], ['reports.*.max' => 'El informe es muy largo. Máximo 2000 letras.']);

        DB::transaction(function () use ($validated, $classroom, $subject, $period, $request) {
            foreach ($classroom->children()->pluck('id') as $childId) {
                $body = trim($validated['reports'][$childId] ?? '');
                $key = ['child_id' => $childId, 'subject_id' => $subject->id, 'academic_period_id' => $period->id];

                if ($body === '') {
                    DimensionReport::where($key)->delete();
                } else {
                    DimensionReport::updateOrCreate($key, ['body' => $body, 'author_id' => $request->user()->id]);
                }
            }
        });

        return $this->backToSheet($classroom, $subject, $period, 'Listo, los informes quedaron guardados.');
    }

    /** Grupos del docente con las materias que dicta en cada uno, en orden escolar. */
    public static function assignmentsOf(User $user)
    {
        return Classroom::whereHas('subjects', fn ($q) => $q->where('classroom_subject.educator_id', $user->id))
            ->with(['subjects' => fn ($q) => $q->wherePivot('educator_id', $user->id)->where('active', true)])
            ->orderBy('name')->get()
            ->sortBy(fn (Classroom $c) => $c->level?->position() ?? PHP_INT_MAX)
            ->values();
    }

    private function ensureTeaches(User $user, Classroom $classroom, Subject $subject): void
    {
        abort_unless($classroom->subjects()->whereKey($subject->id)
            ->wherePivot('educator_id', $user->id)->exists(), 403);
    }

    // Periodo pedido (?periodo=2); si no, el de hoy; si hoy no cae en ninguno, el primero sin cerrar.
    private function resolvePeriod(SchoolYear $year, int $number): ?AcademicPeriod
    {
        return $year->periods->firstWhere('number', $number)
            ?? $year->periodOn()
            ?? $year->periods->first(fn (AcademicPeriod $p) => ! $p->isClosed())
            ?? $year->periods->last();
    }

    // Periodo del formulario, validado: del año actual de este colegio y sin cerrar.
    private function editablePeriod(Request $request, Classroom $classroom, Subject $subject): AcademicPeriod
    {
        $this->ensureTeaches($request->user(), $classroom, $subject);

        $period = SchoolYear::current()?->periods()->where('number', $request->integer('periodo'))->first();
        abort_unless($period, 404);
        abort_if($period->isClosed(), 403, 'Este periodo ya está cerrado.');

        return $period;
    }

    private function assessments(Classroom $classroom, Subject $subject, AcademicPeriod $period)
    {
        return Assessment::where('classroom_id', $classroom->id)
            ->where('subject_id', $subject->id)
            ->where('academic_period_id', $period->id)
            ->orderBy('id');
    }

    private function backToSheet(Classroom $classroom, Subject $subject, AcademicPeriod $period, string $status): RedirectResponse
    {
        return redirect()->route('educator.grades.show', [$classroom, $subject, 'periodo' => $period->number])->with('status', $status);
    }
}
