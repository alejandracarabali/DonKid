<?php

use App\Enums\SchoolType;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\QueryException;

test('la migración crea el Colegio Demo de tipo jardín y primaria', function () {
    $school = School::first();

    expect($school->name)->toBe('Colegio Demo')
        ->and($school->type)->toBe(SchoolType::Both);
});

test('por defecto usuarios, grupos y niños quedan en el Colegio Demo', function () {
    $demo = School::first();

    $educator = User::factory()->educator()->create();
    $child = Child::factory()->create();

    expect($educator->school->is($demo))->toBeTrue()
        ->and($child->classroom->school->is($demo))->toBeTrue()
        ->and($child->school->is($demo))->toBeTrue();
});

test('un niño queda en el mismo colegio que su grupo', function () {
    $otro = School::factory()->create();
    $grupo = Classroom::factory()->create(['school_id' => $otro->id]);

    $child = Child::factory()->create(['classroom_id' => $grupo->id]);

    expect($child->school->is($otro))->toBeTrue();
});

test('no se puede borrar un colegio que todavía tiene datos', function () {
    $school = School::factory()->create();
    User::factory()->create(['school_id' => $school->id]);

    expect(fn () => $school->delete())->toThrow(QueryException::class);
    expect(School::find($school->id))->not->toBeNull();
});

test('un colegio sin datos sí se puede borrar', function () {
    $school = School::factory()->create();

    $school->delete();

    expect(School::find($school->id))->toBeNull();
});