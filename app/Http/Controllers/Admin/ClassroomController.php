<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClassroomRequest;
use App\Models\Classroom;

class ClassroomController extends Controller
{
    // Listado. withCount('children') agrega la cantidad de niños
    // con UNA sola consulta (evita el problema N+1).
    public function index()
    {
        $classrooms = Classroom::withCount('children')->orderBy('name')->get();

        return view('admin.classrooms.index', compact('classrooms'));
    }

    public function create()
    {
        return view('admin.classrooms.create');
    }

    // $request->validated() trae SOLO los datos que pasaron la validación.
    public function store(ClassroomRequest $request)
    {
        Classroom::create($request->validated());

        return redirect()->route('admin.classrooms.index')
            ->with('status', 'Listo, el grupo quedó guardado.');
    }

    // Laravel carga el grupo solo gracias al parámetro {classroom} de la ruta.
    public function edit(Classroom $classroom)
    {
        return view('admin.classrooms.edit', compact('classroom'));
    }

    public function update(ClassroomRequest $request, Classroom $classroom)
    {
        $classroom->update($request->validated());

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
}