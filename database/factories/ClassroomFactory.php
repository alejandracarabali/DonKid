<?php

namespace Database\Factories;

use App\Enums\GradeLevel;
use App\Models\Classroom;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Classroom>
 */
class ClassroomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'school_id' => fn () => SchoolFactory::defaultId(),            
            // Closure: solo se genera si el test no da su propio nombre
            // (la lista tiene 4 nombres únicos; sin esto, crear 5+ grupos con nombre fallaba).
            'name' => fn () => fake()->unique()->randomElement([
                'Caminadores', 'Exploradores', 'Pequeños Artistas', 'Mariposas',
            ]),
            'age_range' => fake()->randomElement(['1 a 2 años', '2 a 3 años', '3 a 4 años']),
            // Preescolar por defecto: así el grupo lleva bitácora en cualquier colegio con jardín.
            'level' => GradeLevel::Jardin,
        ];
    }

    public function level(GradeLevel $level): static
    {
        return $this->state(['level' => $level]);
    }
}
