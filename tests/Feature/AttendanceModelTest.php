<?php

use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Child;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use Illuminate\Database\QueryException;

test('no permite dos asistencias del mismo estudiante el mismo día', function () {
    $child = Child::factory()->create();
    Attendance::factory()->create(['child_id' => $child->id]);

    expect(fn () => Attendance::factory()->create(['child_id' => $child->id]))
        ->toThrow(QueryException::class);
});

test('guarda el estado como enum', function () {
    $attendance = Attendance::factory()->create(['status' => AttendanceStatus::Late]);

    expect($attendance->fresh()->status)->toBe(AttendanceStatus::Late)
        ->and($attendance->status->label())->toBe('Tarde');
});

test('una docente no ve asistencias de otro colegio', function () {
    $otro = School::factory()->create();
    $grupoAjeno = Classroom::factory()->create(['school_id' => $otro->id]);
    Attendance::factory()->create([
        'child_id' => Child::factory()->create(['classroom_id' => $grupoAjeno->id])->id,
    ]);
    Attendance::factory()->create();

    $this->actingAs(User::factory()->educator()->create());

    expect(Attendance::count())->toBe(1);
});