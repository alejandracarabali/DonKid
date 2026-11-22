<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * RF-04: qué materias se dictan en un grupo y qué docente dicta cada una.
 */
class ClassroomSubjectController extends Controller
{
    public function edit(Classroom $classroom): View
    {
        if (! $classroom->level) {
            return view('admin.classrooms.subjects', ['classroom' => $classroom, 'subjects' => collect(), 'educators' => collect(), 'assigned' => collect()]);
        }

        return view('admin.classrooms.subjects', [
            'classroom' => $classroom,
            'subjects' => Subject::active()->where('kind', $classroom->subjectKind())->orderBy('position')->get(),
            'educators' => User::where('role', Role::Educator)->orderBy('name')->get(['id', 'name']),
            // subject_id => educator_id (null = se dicta, sin docente aún)
            'assigned' => $classroom->subjects()->pluck('educator_id', 'subjects.id'),
        ]);
    }

    public function update(Request $request, Classroom $classroom): RedirectResponse
    {
        abort_unless($classroom->level, 422, 'El grupo necesita un nivel.');

        $subjectIds = Subject::active()->where('kind', $classroom->subjectKind())->pluck('id');
        $schoolId = $request->user()->school_id;

        $data = $request->validate([
            'subjects' => ['array'],
            'subjects.*.on' => ['nullable', 'boolean'],
            'subjects.*.educator_id' => ['nullable', Rule::exists('users', 'id')->where('school_id', $schoolId)->where('role', Role::Educator->value)],
        ], ['subjects.*.educator_id.exists' => 'Elige un docente de tu colegio.']);

        $sync = [];
        foreach ($data['subjects'] ?? [] as $subjectId => $row) {
            if ($subjectIds->contains((int) $subjectId) && ! empty($row['on'])) {
                $sync[$subjectId] = ['educator_id' => $row['educator_id'] ?? null];
            }
        }

        DB::transaction(function () use ($classroom, $sync) {
            $classroom->subjects()->sync($sync);
            // El docente de una materia también ve el grupo (asistencia, lista de niños).
            $educators = collect($sync)->pluck('educator_id')->filter()->unique()->all();
            $classroom->educators()->syncWithoutDetaching($educators);
        });

        return redirect()->route('admin.classrooms.index')->with('status', "Listo, quedaron guardadas las materias de {$classroom->name}.");
    }
}
