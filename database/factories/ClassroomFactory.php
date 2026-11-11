<?php

namespace Database\Factories;

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
            'name' => fake()->unique()->randomElement([
                'Caminadores', 'Exploradores', 'Pequeños Artistas', 'Mariposas',
            ]),
            'age_range' => fake()->randomElement(['1 a 2 años', '2 a 3 años', '3 a 4 años']),
        ];
    }
}
