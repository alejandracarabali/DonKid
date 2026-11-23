<?php

use App\Enums\GradeLevel;
use App\Models\Classroom;
use App\Models\User;

it('lista los grupos en orden escolar y los que no tienen nivel al final', function () {
    Classroom::factory()->create(['name' => 'Cuarto', 'level' => GradeLevel::Cuarto]);
    Classroom::factory()->create(['name' => 'Sin nivel', 'level' => null]);
    Classroom::factory()->create(['name' => 'Jardín', 'level' => GradeLevel::Jardin]);
    Classroom::factory()->create(['name' => 'Párvulos', 'level' => GradeLevel::Parvulos]);
    Classroom::factory()->create(['name' => 'Primero', 'level' => GradeLevel::Primero]);

    $this->actingAs(User::factory()->admin()->create())
        ->get(route('admin.classrooms.index'))
        ->assertSeeInOrder(['Párvulos', 'Jardín', 'Primero', 'Cuarto', 'Sin nivel']);
});