<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildRequest;
use App\Models\Child;
use App\Models\Classroom;

class ChildController extends Controller
{
    // with('classroom') carga los grupos de todos los niños en UNA consulta
    // extra, en vez de una por niño (el problema N+1).
    public function index()
    {
        $children = Child::with('classroom')
            ->orderBy('first_name')
            ->paginate(15); // paginar = rápido aunque haya cientos de niños

        return view('admin.children.index', compact('children'));
    }

    public function create()
    {
        return view('admin.children.create', [
            'classrooms' => Classroom::orderBy('name')->get(),
        ]);
    }

    public function store(ChildRequest $request)
    {
        Child::create($request->validated());

        return redirect()->route('admin.children.index')
            ->with('status', 'Listo, el niño quedó guardado.');
    }

    public function edit(Child $child)
    {
        return view('admin.children.edit', [
            'child' => $child,
            'classrooms' => Classroom::orderBy('name')->get(),
        ]);
    }

    public function update(ChildRequest $request, Child $child)
    {
        $child->update($request->validated());

        return redirect()->route('admin.children.index')
            ->with('status', 'Listo, cambios guardados.');
    }

    // Se borra al niño y, por cascadeOnDelete, también sus vínculos con acudientes.
    public function destroy(Child $child)
    {
        $child->delete();

        return redirect()->route('admin.children.index')
            ->with('status', 'El niño se quitó de la lista.');
    }
}