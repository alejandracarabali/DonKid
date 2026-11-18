<?php

namespace App\Http\Controllers\Educator;

use App\Enums\AttendanceStatus;
use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class GroupController extends Controller
{
    // "Mis grupos": cada grupo con cuántos estudiantes tienen asistencia hoy.
    public function index(Request $request): View
    {
        $classrooms = $this->classroomsOf($request->user())
            ->withCount([
                'children',
                'children as recorded_count' => fn ($q) => $q->whereHas(
                    'attendances',
                    fn ($a) => $a->whereDate('date', today())
                ),
            ])
            ->orderBy('name')
            ->get();

        return view('educator.groups.index', ['classrooms' => $classrooms]);
    }

    public function attendance(Request $request, Classroom $classroom): View
    {
        $this->ensureCanManage($request->user(), $classroom);

        $children = $classroom->children()
            ->with(['attendances' => fn ($q) => $q->whereDate('date', today())])
            ->orderBy('first_name')
            ->get();

        return view('educator.groups.attendance', [
            'classroom' => $classroom,
            'children' => $children,
            'statuses' => AttendanceStatus::cases(),
            // Estado de hoy de cada estudiante: [id => 'present' | 'late' | 'absent' | null].
            'marks' => $children->mapWithKeys(
                fn ($child) => [$child->id => $child->attendances->first()?->status->value]
            )->all(),
        ]);
    }

    public function storeAttendance(Request $request, Classroom $classroom): RedirectResponse
    {
        $user = $request->user();
        $this->ensureCanManage($user, $classroom);

        $validated = $request->validate([
            'status' => ['required', 'array'],
            'status.*' => [Rule::enum(AttendanceStatus::class)],
        ], [
            'status.required' => 'Marca la asistencia de al menos un estudiante.',
            'status.*' => 'Elige Presente, Tarde o Ausente.',
        ]);

        // Se recorren SOLO los estudiantes de este grupo. Si alguien agrega a mano
        // el id de un estudiante de otro grupo en la petición, simplemente se ignora.
        DB::transaction(function () use ($classroom, $validated, $user) {
            foreach ($classroom->children()->pluck('id') as $childId) {
                $status = $validated['status'][$childId] ?? null;

                if ($status === null) {
                    continue; // Sin marcar: se puede guardar a medias y completar después.
                }

                $attendance = Attendance::where('child_id', $childId)->whereDate('date', today())->first()
                    ?? new Attendance(['child_id' => $childId, 'date' => today()]);

                $attendance->status = $status;
                $attendance->recorded_by = $user->id; // Lo asigna el servidor, nunca el formulario.
                $attendance->save();
            }
        });

        return redirect()->route('educator.groups.attendance', $classroom)
            ->with('status', 'Listo, la asistencia de '.$classroom->name.' quedó guardada.');
    }

    // Coordinación ve todos los grupos de su colegio; la docente, solo los suyos.
    private function classroomsOf(User $user): Builder|BelongsToMany
    {
        return $user->hasRole(Role::Admin) ? Classroom::query() : $user->classrooms();
    }

    private function ensureCanManage(User $user, Classroom $classroom): void
    {
        abort_unless(
            $user->hasRole(Role::Admin) || $user->classrooms()->whereKey($classroom->id)->exists(),
            403
        );
    }
}