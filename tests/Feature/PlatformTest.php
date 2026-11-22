<?php

use App\Enums\Role;
use App\Http\Middleware\EnsureTwoFactor;
use App\Enums\SchoolType;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;

// RF-55 a RF-57: superadministrador de la plataforma DonKid.

// Superadmin con la verificación en dos pasos configurada y ya superada en esta sesión.
function superAdminVerified(): User
{
    $user = User::factory()->superAdmin()->withTwoFactor()->create();
    test()->withSession([EnsureTwoFactor::SESSION_KEY => $user->id]);

    return $user;
}

it('crea el superadministrador por consola, sin colegio y con clave temporal', function () {
    $this->artisan('donkid:superadmin', ['email' => 'equipo@donkid.co'])
        ->expectsOutputToContain('Superadministrador creado')
        ->assertSuccessful();

    $user = User::withoutGlobalScopes()->where('email', 'equipo@donkid.co')->first();

    expect($user->role)->toBe(Role::SuperAdmin)
        ->and($user->school_id)->toBeNull()
        ->and($user->must_change_password)->toBeTrue();
});

it('el comando rechaza un correo que ya existe', function () {
    User::factory()->admin()->create(['email' => 'equipo@donkid.co']);

    $this->artisan('donkid:superadmin', ['email' => 'equipo@donkid.co'])->assertFailed();
});

it('el superadministrador llega a Colegios al iniciar sesión', function () {
    $this->actingAs(superAdminVerified())
        ->get('/dashboard')
        ->assertRedirect(route('platform.schools.index'));
});

it('lista los colegios con estudiantes activos, sin datos personales', function () {
    $colegio = School::query()->orderBy('id')->first();
    Child::factory()->count(2)->create(['first_name' => 'Secreto']);
    Child::factory()->create()->withdraw(today()->toDateString(), null);

    $this->actingAs(superAdminVerified())
        ->get(route('platform.schools.index'))
        ->assertOk()
        ->assertSee($colegio->name)
        ->assertSeeInOrder([$colegio->name, '2'])
        ->assertDontSee('Secreto');
});

it('crea un colegio con su primera cuenta de coordinación', function () {
    $this->actingAs(superAdminVerified())
        ->post(route('platform.schools.store'), [
            'name' => 'Jardín Los Pinos',
            'type' => 'kindergarten',
            'admin_name' => 'Laura Pérez',
            'admin_email' => 'laura@lospinos.co',
        ])
        ->assertRedirect(route('platform.schools.index'))
        ->assertSessionHas('temporal_password');

    $school = School::where('slug', 'jardin-los-pinos')->first();
    $admin = User::withoutGlobalScopes()->where('email', 'laura@lospinos.co')->first();

    expect($school->type)->toBe(SchoolType::Kindergarten)
        ->and($admin->role)->toBe(Role::Admin)
        ->and($admin->school_id)->toBe($school->id)
        ->and($admin->must_change_password)->toBeTrue();
});

it('no crea un colegio con un slug repetido ni un correo ocupado', function () {
    $superAdmin = superAdminVerified();
    User::factory()->admin()->create(['email' => 'ocupado@correo.co']);
    $existente = School::query()->orderBy('id')->first();

    $this->actingAs($superAdmin)->post(route('platform.schools.store'), [
        'name' => 'Otro', 'slug' => $existente->slug, 'type' => 'both',
        'admin_name' => 'X', 'admin_email' => 'ocupado@correo.co',
    ])->assertSessionHasErrors(['slug', 'admin_email']);
});

it('al suspender un colegio nadie de él puede ingresar, y sus datos se conservan', function () {
    $school = School::query()->orderBy('id')->first();
    $docente = User::factory()->educator()->create();
    Child::factory()->create();

    $this->actingAs(superAdminVerified())
        ->post(route('platform.schools.suspend', $school));

    $this->actingAs($docente->fresh())
        ->get(route('educator.reports.index'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status');
    $this->assertGuest();

    expect(Child::withoutGlobalScopes()->where('school_id', $school->id)->count())->toBe(1);
});

it('al reactivar, el colegio vuelve a ingresar', function () {
    $school = School::query()->orderBy('id')->first();
    $school->suspend();
    $admin = User::factory()->admin()->create();

    $this->actingAs(superAdminVerified())
        ->post(route('platform.schools.reactivate', $school));

    $this->actingAs($admin->fresh())->get(route('admin.dashboard'))->assertOk();
});

it('suspender un colegio no afecta a otro', function () {
    $otro = School::factory()->create();
    $adminOtro = User::factory()->admin()->create(['school_id' => $otro->id]);
    School::query()->orderBy('id')->first()->suspend();

    $this->actingAs($adminOtro)->get(route('admin.dashboard'))->assertOk();
});

describe('el superadministrador no ve datos de los colegios', function () {
    it('no entra a las zonas de coordinación, docente ni familia', function (string $url) {
        $this->actingAs(superAdminVerified())->get($url)->assertForbidden();
    })->with(['/admin', '/admin/ninos', '/admin/inscripciones', '/docente/bitacora', '/familia']);

    it('las consultas de datos de colegio no le devuelven nada', function () {
        Child::factory()->count(3)->create();
        Classroom::factory()->create();

        $this->actingAs(superAdminVerified());

        expect(Child::count())->toBe(0)
            ->and(Classroom::count())->toBe(0);
    });

    it('ninguna policy le da permiso', function () {
        $superAdmin = superAdminVerified();
        $nino = Child::withoutGlobalScopes()->find(Child::factory()->create()->id);

        expect($superAdmin->can('view', $nino))->toBeFalse();
    });
});

it('solo el superadministrador entra a la plataforma', function (string $role) {
    $this->actingAs(User::factory()->{$role}()->create())
        ->get(route('platform.schools.index'))
        ->assertForbidden();
})->with(['admin', 'educator', 'guardian']);

describe('buscador de colegios', function () {
    beforeEach(function () {
        School::factory()->create(['name' => 'Jardín Los Pinos', 'slug' => 'jardin-los-pinos']);
        School::factory()->create(['name' => 'Colegio San Rafael', 'slug' => 'san-rafael']);
        School::factory()->create(['name' => 'Gimnasio Pequeños', 'slug' => 'gimnasio-pequenos', 'suspended_at' => now()]);
    });

    it('busca por nombre', function () {
        $this->actingAs(superAdminVerified())
            ->get(route('platform.schools.index', ['q' => 'pinos']))
            ->assertOk()
            ->assertSee('Jardín Los Pinos')
            ->assertDontSee('Colegio San Rafael');
    });

    it('busca por slug', function () {
        $this->actingAs(superAdminVerified())
            ->get(route('platform.schools.index', ['q' => 'san-rafael']))
            ->assertSee('Colegio San Rafael')
            ->assertDontSee('Jardín Los Pinos');
    });

    it('filtra por estado', function () {
        $superAdmin = superAdminVerified();

        $this->actingAs($superAdmin)->get(route('platform.schools.index', ['estado' => 'suspendidos']))
            ->assertSee('Gimnasio Pequeños')
            ->assertDontSee('Jardín Los Pinos');

        $this->actingAs($superAdmin)->get(route('platform.schools.index', ['estado' => 'activos']))
            ->assertSee('Jardín Los Pinos')
            ->assertDontSee('Gimnasio Pequeños');
    });

    it('avisa cuando no hay resultados', function () {
        $this->actingAs(superAdminVerified())
            ->get(route('platform.schools.index', ['q' => 'inexistente']))
            ->assertSee('No hay colegios que coincidan con la búsqueda');
    });

    it('pagina de a 20 y conserva la búsqueda entre páginas', function () {
        School::factory()->count(25)->sequence(fn ($s) => ['name' => 'Jardín Número '.$s->index, 'slug' => 'jardin-numero-'.$s->index])->create();

        $this->actingAs(superAdminVerified())
            ->get(route('platform.schools.index', ['q' => 'número']))
            ->assertSee('25 colegios')
            ->assertSee('q=n%C3%BAmero', false);
    });
});
