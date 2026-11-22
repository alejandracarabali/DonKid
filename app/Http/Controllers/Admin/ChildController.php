<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildRequest;
use App\Models\Child;
use App\Models\Classroom;
use Illuminate\Http\Request;

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
        $data = $request->validated();
        $child = Child::create(collect($data)->except('health')->all());
        $this->saveHealth($child, $data['health'] ?? []);

        return redirect()->route('admin.children.index')
            ->with('status', 'Listo, el niño quedó guardado.');
    }

    public function edit(Child $child)
    {
        return view('admin.children.edit', [
            'child' => $child->load('healthRecord'),
            'classrooms' => Classroom::orderBy('name')->get(),
        ]);
    }

    public function update(ChildRequest $request, Child $child)
    {
        $data = $request->validated();
        $child->update(collect($data)->except('health')->all());
        $this->saveHealth($child, $data['health'] ?? []);

        return redirect()->route('admin.children.index')
            ->with('status', 'Listo, cambios guardados.');
    }

    // RF-53: retirar NO borra. El niño queda inhabilitado con fecha y motivo,
    // y conserva su historial, bitácora y vínculos con acudientes.
    public function withdraw(Request $request, Child $child)
    {
        $data = $request->validate([
            'withdrawn_at' => ['required', 'date', 'before_or_equal:today'],
            'withdrawal_reason' => ['nullable', 'string', 'max:200'],
        ], [
            'withdrawn_at.required' => 'Elige la fecha de retiro.',
            'withdrawn_at.before_or_equal' => 'La fecha de retiro no puede ser futura.',
            'withdrawal_reason.max' => 'Es muy largo. Máximo 200 letras.',
        ]);

        $child->withdraw($data['withdrawn_at'], $data['withdrawal_reason'] ?? null);

        return redirect()->route('admin.children.index')
            ->with('status', "{$child->first_name} quedó como retirado. Su información se conserva.");
    }

    public function withdrawn()
    {
        $children = Child::withdrawn()
            ->with('classroom')
            ->orderByDesc('withdrawn_at')
            ->paginate(15);

        return view('admin.children.withdrawn', compact('children'));
    }

    // El niño retirado no pasa por el binding normal (el scope 'active' lo oculta),
    // por eso se busca a mano. El scope 'school' sigue aplicando: solo el propio colegio.
    public function reactivate(int $child)
    {
        $child = Child::withdrawn()->findOrFail($child);
        $child->reactivate();

        return redirect()->route('admin.children.index')
            ->with('status', "{$child->first_name} volvió a estar activo.");
    }

    // RF-38: crea o actualiza la ficha de salud (una por niño).
    private function saveHealth(Child $child, array $health): void
    {
        $child->healthRecord()->updateOrCreate([], $health);
    }
}
