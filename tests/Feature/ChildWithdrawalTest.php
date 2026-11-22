<?php

use App\Models\Child;
use App\Models\Classroom;
use App\Models\DailyReport;
use App\Models\User;
use Illuminate\Support\Facades\DB;

// RF-53: un estudiante retirado queda inhabilitado, nunca se borra.

it('retira a un niño sin borrar su información ni sus vínculos', function () {
    $admin = User::factory()->admin()->create();
    $madre = User::factory()->guardian()->create();
    $nino = Child::factory()->create();
    $nino->guardians()->attach($madre->id, ['relationship' => 'Mamá']);
    DailyReport::create(['child_id' => $nino->id, 'report_date' => today(), 'mood' => 'happy']);

    $this->actingAs($admin)
        ->patch(route('admin.children.withdraw', $nino), [
            'withdrawn_at' => today()->toDateString(),
            'withdrawal_reason' => 'Cambio de ciudad',
        ])
        ->assertRedirect(route('admin.children.index'));

    $retirado = Child::withdrawn()->find($nino->id);

    expect($retirado)->not->toBeNull()
        ->and($retirado->withdrawal_reason)->toBe('Cambio de ciudad')
        ->and(Child::find($nino->id))->toBeNull() // ya no aparece en consultas normales
        ->and(DB::table('child_guardian')->where('child_id', $nino->id)->exists())->toBeTrue()
        ->and(DailyReport::where('child_id', $nino->id)->exists())->toBeTrue();
});

it('exige la fecha de retiro y no acepta fechas futuras', function () {
    $admin = User::factory()->admin()->create();
    $nino = Child::factory()->create();

    $this->actingAs($admin)
        ->patch(route('admin.children.withdraw', $nino), [])
        ->assertSessionHasErrors('withdrawn_at');

    $this->actingAs($admin)
        ->patch(route('admin.children.withdraw', $nino), ['withdrawn_at' => now()->addDay()->toDateString()])
        ->assertSessionHasErrors('withdrawn_at');

    expect(Child::find($nino->id))->not->toBeNull();
});

it('ya no permite borrar niños', function () {
    $admin = User::factory()->admin()->create();
    $nino = Child::factory()->create();

    $this->actingAs($admin)->delete('/admin/ninos/'.$nino->id)->assertStatus(405);

    expect(Child::find($nino->id))->not->toBeNull();
});

it('oculta al retirado de la lista activa y lo muestra entre los retirados', function () {
    $admin = User::factory()->admin()->create();
    Child::factory()->create(['first_name' => 'Activa']);
    Child::factory()->create(['first_name' => 'Retirada'])->withdraw(today()->toDateString(), null);

    $this->actingAs($admin)->get(route('admin.children.index'))
        ->assertOk()->assertSee('Activa')->assertDontSee('Retirada');

    $this->actingAs($admin)->get(route('admin.children.withdrawn'))
        ->assertOk()->assertSee('Retirada')->assertDontSee('Activa');
});

it('reactiva a un niño retirado', function () {
    $admin = User::factory()->admin()->create();
    $nino = Child::factory()->create();
    $nino->withdraw(today()->toDateString(), 'Viaje');

    $this->actingAs($admin)
        ->patch(route('admin.children.reactivate', $nino->id))
        ->assertRedirect(route('admin.children.index'));

    expect(Child::find($nino->id)?->withdrawn_at)->toBeNull();
});

it('saca al retirado de la bitácora de su docente', function () {
    $grupo = Classroom::factory()->create();
    $docente = User::factory()->educator()->create();
    $docente->classrooms()->attach($grupo);
    Child::factory()->create(['classroom_id' => $grupo->id, 'first_name' => 'Presente']);
    Child::factory()->create(['classroom_id' => $grupo->id, 'first_name' => 'Ausente'])
        ->withdraw(today()->toDateString(), null);

    $this->actingAs($docente)->get(route('educator.reports.index'))
        ->assertOk()->assertSee('Presente')->assertDontSee('Ausente');
});

it('quita el acceso al acudiente cuyo único hijo fue retirado', function () {
    $madre = User::factory()->guardian()->create();
    $nino = Child::factory()->create();
    $madre->children()->attach($nino);
    $nino->withdraw(today()->toDateString(), null);

    $this->actingAs($madre)
        ->get(route('guardian.dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('status');

    $this->assertGuest();
});

it('mantiene el acceso si el acudiente tiene otro hijo activo', function () {
    $madre = User::factory()->guardian()->create();
    $retirado = Child::factory()->create(['first_name' => 'Mayor']);
    $activo = Child::factory()->create(['first_name' => 'Menor']);
    $madre->children()->attach([$retirado->id, $activo->id]);
    $retirado->withdraw(today()->toDateString(), null);

    $this->actingAs($madre)
        ->get(route('guardian.dashboard'))
        ->assertOk()->assertSee('Menor')->assertDontSee('Mayor');
});

it('no bloquea a una familia recién creada sin hijos vinculados', function () {
    $madre = User::factory()->guardian()->create();

    $this->actingAs($madre)->get(route('guardian.dashboard'))->assertOk();
});

it('conserva el vínculo con el retirado al editar la familia', function () {
    $admin = User::factory()->admin()->create();
    $madre = User::factory()->guardian()->create();
    $retirado = Child::factory()->create();
    $activo = Child::factory()->create();
    $madre->children()->attach([$retirado->id => ['relationship' => 'Mamá'], $activo->id => ['relationship' => 'Mamá']]);
    $retirado->withdraw(today()->toDateString(), null);

    $this->actingAs($admin)->patch(route('admin.guardians.update', $madre), [
        'name' => $madre->name,
        'email' => $madre->email,
        'children' => [$activo->id],
        'relationships' => [$activo->id => 'Mamá'],
    ])->assertSessionHasNoErrors();

    expect(DB::table('child_guardian')
        ->where(['child_id' => $retirado->id, 'user_id' => $madre->id, 'relationship' => 'Mamá'])
        ->exists())->toBeTrue();
});
