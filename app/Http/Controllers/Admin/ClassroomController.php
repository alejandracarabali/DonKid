<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassroomRequest;
use App\Enums\Role;
use App\Models\Classroom;
use App\Models\User;

class ClassroomController extends Controller
{
    // Listado. withCount('children') agrega la cantidad de niños
    // con UNA sola consulta (evita el problema N+1).
    public function index()
    {
        $classrooms = Classroom::with('director')->withCount('children')->orderBy('name')->get();

        return view('admin.classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        return view('admin.classrooms.create', ['educators' => $this->educators()]);
    }

    // $request->validated() trae SOLO los datos que pasaron la validación.
    public function store(ClassroomRequest $request)
    {
        $classroom = Classroom::create($request->validated());
        $this->attachDirector($classroom);

        return redirect()->route('admin.classrooms.index')
            ->with('status', 'Listo, el grupo quedó guardado.');
    }

    // Laravel carga el grupo solo gracias al parámetro {classroom} de la ruta.
    public function edit(Classroom $classroom)
    {
        return view('admin.classrooms.edit', ['classroom' => $classroom, 'educators' => $this->educators()]);
    }

    public function update(ClassroomRequest $request, Classroom $classroom)
    {
        $classroom->update($request->validated());
        $this->attachDirector($classroom);

        return redirect()->route('admin.classrooms.index')
            ->with('status', 'Listo, cambios guardados.');
    }

    // Los niños del grupo NO se borran: nullOnDelete los deja "sin grupo".
    public function destroy(Classroom $classroom)
    {
        $classroom->delete();

        return redirect()->route('admin.classrooms.index')
            ->with('status', 'El grupo se quitó. Sus niños quedaron sin grupo.');
    }

    private function educators()
    {
        return User::where('role', Role::Educator)->orderBy('name')->get(['id', 'name']);
    }

    // El director también es docente del grupo: se asigna si no lo estaba.
    private function attachDirector(Classroom $classroom): void
    {
        if ($classroom->director_id) {
            $classroom->educators()->syncWithoutDetaching([$classroom->director_id]);
        }
    }
}
