<?php

namespace App\Http\Controllers\Platform;

use App\Enums\Role;
use App\Enums\SchoolType;
use App\Http\Controllers\Controller;
use App\Http\Requests\PlatformSchoolRequest;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * RF-55 a RF-57: el superadministrador gestiona los colegios de DonKid.
 * Solo ve datos agregados (cuántos estudiantes), nunca datos personales.
 */
class SchoolController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));
        $status = $request->query('estado', 'todos');

        $schools = School::query()
            // Buscar por nombre o por slug (la dirección del enlace de inscripción).
            ->when($search !== '', fn ($q) => $q->where(fn ($q) => $q
                ->where('name', 'like', '%'.$search.'%')
                ->orWhere('slug', 'like', '%'.$search.'%')))
            ->when($status === 'activos', fn ($q) => $q->whereNull('suspended_at'))
            ->when($status === 'suspendidos', fn ($q) => $q->whereNotNull('suspended_at'))
            // El conteo ignora los filtros de colegio y de activos, y luego cuenta solo los activos.
            ->withCount(['children as active_children_count' => fn ($q) => $q->withoutGlobalScopes()->whereNull('withdrawn_at')])
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString(); // conserva la búsqueda al cambiar de página

        return view('platform.schools.index', compact('schools', 'search', 'status'));
    }

    public function create(): View
    {
        return view('platform.schools.create', ['types' => SchoolType::cases()]);
    }

    public function store(PlatformSchoolRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $temporal = Str::password(10, symbols: false);

        // El colegio y su primera cuenta de coordinación se crean juntos, o nada.
        $admin = DB::transaction(function () use ($data, $temporal) {
            $school = School::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'type' => $data['type'],
                'plan' => $data['plan'] ?? null,
            ]);

            $admin = new User(['name' => $data['admin_name'], 'email' => $data['admin_email'], 'password' => $temporal]);
            $admin->role = Role::Admin;
            $admin->school_id = $school->id;
            $admin->must_change_password = true;
            $admin->email_verified_at = now();
            $admin->save();

            return $admin;
        });

        return redirect()->route('platform.schools.index')
            ->with('status', "Listo, {$data['name']} quedó creado.")
            ->with('temporal_password', ['email' => $admin->email, 'password' => $temporal]);
    }

    public function suspend(School $school): RedirectResponse
    {
        $school->suspend();

        return back()->with('status', "{$school->name} quedó suspendido. Sus datos se conservan.");
    }

    public function reactivate(School $school): RedirectResponse
    {
        $school->reactivate();

        return back()->with('status', "{$school->name} volvió a estar activo.");
    }
}
