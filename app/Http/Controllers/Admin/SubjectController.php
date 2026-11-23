<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SubjectKind;
use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * RF-04: coordinación ajusta las materias (primaria) y dimensiones (preescolar) de su colegio.
 */
class SubjectController extends Controller
{
    public function index(Request $request): View
    {
        $kinds = SubjectKind::forSchool($request->user()->school?->type);

        return view('admin.subjects.index', [
            'kinds' => $kinds,
            'subjects' => Subject::orderBy('position')->orderBy('name')->get()->groupBy(fn (Subject $s) => $s->kind->value),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $kinds = SubjectKind::forSchool($request->user()->school?->type);

        $data = $request->validate([
            'kind' => ['required', Rule::enum(SubjectKind::class)->only($kinds)],
            'name' => ['required', 'string', 'max:80',
                Rule::unique('subjects')->where('school_id', $request->user()->school_id)->where('kind', $request->input('kind'))],
        ], [
            'name.required' => 'Escribe el nombre.',
            'name.unique' => 'Ya existe con ese nombre.',
        ]);

        Subject::create($data + ['position' => Subject::where('kind', $data['kind'])->max('position') + 1]);

        return back()->with('status', "Listo, se agregó {$data['name']}.");
    }

    public function update(Request $request, Subject $subject): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80',
                Rule::unique('subjects')->where('school_id', $subject->school_id)->where('kind', $subject->kind->value)->ignore($subject)],
        ], [
            'name.required' => 'Escribe el nombre.',
            'name.unique' => 'Ya existe con ese nombre.',
        ]);

        $subject->update($data);

        return back()->with('status', 'Listo, cambio guardado.');
    }

    // Activar o desactivar. No se borra: más adelante puede tener notas guardadas.
    public function toggle(Subject $subject): RedirectResponse
    {
        $subject->update(['active' => ! $subject->active]);

        return back()->with('status', $subject->active ? "{$subject->name} está activa." : "{$subject->name} quedó desactivada.");
    }
}
