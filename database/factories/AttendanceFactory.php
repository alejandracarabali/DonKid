<?php

namespace Database\Factories;

use App\Enums\AttendanceStatus;
use App\Models\Child;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Attendance>
 */
class AttendanceFactory extends Factory
{
    // Valores por defecto de una asistencia de prueba.
    // Cada test puede cambiar el que necesite, por ejemplo:
    // Attendance::factory()->create(['status' => AttendanceStatus::Late])
    public function definition(): array
    {
        return [
            'child_id' => Child::factory(),
            'date' => today(),
            'status' => AttendanceStatus::Present,
            'recorded_by' => User::factory()->educator(),
        ];
    }
}