<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\EducatorRequest;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Support\Str;

class EducatorController extends Controller
{
    // with('classrooms') evita el N+1 al mostrar los grupos de cada educadora.
    public function index()
    {
        $educators = User::where('role', Role::Educator->value)
            ->with('classrooms')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.educators.index', compact('educators'));
    }

    public function create()
    {
        return view('admin.educators.create', [
            'classrooms' => Classroom::orderBy('name')->get(),
        ]);
    }

    public function store(EducatorRequest $request)
    {
        $data = $request->validated();
        $temporal = Str::password(10, symbols: false);

        $educator = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $temporal,
        ]);

        // role y must_change_password se asignan en código nuestro, nunca desde el formulario.
        $educator->role = Role::Educator;
        $educator->must_change_password = true;
        $educator->email_verified_at = now();
        $educator->save();

        $educator->classrooms()->sync($data['classrooms'] ?? []);

        return redirect()->route('admin.educators.index')
            ->with('status', 'Listo, la cuenta quedó creada.')
            ->with('temporal_password', ['email' => $educator->email, 'password' => $temporal]);
    }

    public function edit(User $educator)
    {
        abort_unless($educator->role === Role::Educator, 404);

        return view('admin.educators.edit', [
            'educator' => $educator->load('classrooms'),
            'classrooms' => Classroom::orderBy('name')->get(),
        ]);
    }

    public function update(EducatorRequest $request, User $educator)
    {
        abort_unless($educator->role === Role::Educator, 404);

        $data = $request->validated();
        $educator->update(['name' => $data['name'], 'email' => $data['email']]);
        $educator->classrooms()->sync($data['classrooms'] ?? []);

        return redirect()->route('admin.educators.index')
            ->with('status', 'Listo, cambios guardados.');
    }

    public function destroy(User $educator)
    {
        abort_unless($educator->role === Role::Educator, 404);

        // cascadeOnDelete borra solo los vínculos; los grupos siguen existiendo.
        $educator->delete();

        return redirect()->route('admin.educators.index')
            ->with('status', 'La cuenta se quitó. Los grupos siguen en la lista.');
    }
}