<?php

use App\Enums\GradeLevel;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Blade;

// Botones de ícono: el nombre accesible lleva la acción y a quién afecta.

it('el botón de ícono tiene nombre accesible y tooltip', function () {
    $html = Blade::render('<x-icon-button icon="edit" label="Editar" context="Ana Ruiz" href="/x" />');

    expect($html)->toContain('aria-label="Editar Ana Ruiz"')
        ->toContain('href="/x"')
        ->toContain('>Editar</span>');
});

it('materias: la desactivada se atenúa y ofrece Activar', function () {
    $this->actingAs(User::factory()->admin()->create());
    Subject::where('name', 'Inglés')->update(['active' => false]);

    $this->get(route('admin.subjects.index'))
        ->assertSee('aria-label="Desactivar Matemáticas"', false)
        ->assertSee('aria-label="Activar Inglés"', false)
        ->assertSee('opacity-60');
});

it('niños: Retirar lleva a la sección de retiro de Editar', function () {
    $this->actingAs(User::factory()->admin()->create());
    $child = Child::factory()->create(['first_name' => 'Ana', 'last_name' => 'Ruiz']);

    $this->get(route('admin.children.index'))
        ->assertSee('aria-label="Retirar Ana Ruiz"', false)
        ->assertSee(route('admin.children.edit', $child).'#retirar', false);
    $this->get(route('admin.children.edit', $child))->assertSee('id="retirar"', false);
});

it('grupos y familias confirman en la misma fila, sin confirm() del navegador', function () {
    $this->actingAs(User::factory()->admin()->create());
    Classroom::factory()->create(['name' => "O'Higgins", 'level' => GradeLevel::Primero]);
    User::factory()->guardian()->create(['name' => "Ana O'Neil"]);

    $this->get(route('admin.classrooms.index'))->assertSee('Sí, quitar')->assertDontSee('confirm(', false);
    $this->get(route('admin.guardians.index'))->assertSee('Sí, quitar')->assertDontSee('confirm(', false);
});
