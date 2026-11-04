<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Requests\GuardianRequest;
use App\Models\Child;
use App\Models\User;
use Illuminate\Support\Str;

class GuardianController extends Controller
{
    // Solo cuentas con rol familia. withCount evita el problema N+1.
    public function index()
    {
        $guardians = User::where('role', Role::Guardian->value)
            ->withCount('children')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.guardians.index', compact('guardians'));
    }

    public function create()
    {
        return view('admin.guardians.create', [
            'children' => Child::orderBy('first_name')->get(),
        ]);
    }

    public function store(GuardianRequest $request)
    {
        $data = $request->validated();

        // Contraseña temporal aleatoria. El cast 'hashed' del modelo la encripta sola.
        $temporal = Str::password(10, symbols: false);

        $guardian = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $temporal,
        ]);

        // 'role' NO está en $fillable (anti mass assignment), así que se asigna
        // a mano, en código que controlamos nosotros. Nunca viene del formulario.
        $guardian->role = Role::Guardian;
        $guardian->email_verified_at = now();
        $guardian->save();

        $this->syncChildren($guardian, $data);

        // with(): se guarda en sesión y se muestra UNA sola vez.
        return redirect()->route('admin.guardians.index')
            ->with('status', 'Listo, la cuenta quedó creada.')
            ->with('temporal_password', ['email' => $guardian->email, 'password' => $temporal]);
    }

    public function edit(User $guardian)
    {
        // Seguridad: este CRUD solo gestiona cuentas de familia.
        abort_unless($guardian->role === Role::Guardian, 404);

        return view('admin.guardians.edit', [
            'guardian' => $guardian->load('children'),
            'children' => Child::orderBy('first_name')->get(),
        ]);
    }

    public function update(GuardianRequest $request, User $guardian)
    {
        abort_unless($guardian->role === Role::Guardian, 404);

        $data = $request->validated();
        $guardian->update(['name' => $data['name'], 'email' => $data['email']]);
        $this->syncChildren($guardian, $data);

        return redirect()->route('admin.guardians.index')
            ->with('status', 'Listo, cambios guardados.');
    }

    public function destroy(User $guardian)
    {
        abort_unless($guardian->role === Role::Guardian, 404);

        // cascadeOnDelete borra solo los vínculos; los niños siguen existiendo.
        $guardian->delete();

        return redirect()->route('admin.guardians.index')
            ->with('status', 'La cuenta se quitó. Los niños siguen en la lista.');
    }

    // Arma el arreglo [id_niño => ['relationship' => 'Mamá']] y sincroniza.
    private function syncChildren(User $guardian, array $data): void
    {
        $sync = [];
        foreach ($data['children'] ?? [] as $childId) {
            $sync[$childId] = ['relationship' => $data['relationships'][$childId] ?? null];
        }

        $guardian->children()->sync($sync);
    }
}