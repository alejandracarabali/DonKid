<?php

namespace Database\Factories;

use App\Models\Classroom;
use App\Models\Child;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Child>
 */
class ChildFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'classroom_id' => Classroom::factory(),
            // El niño queda en el mismo colegio que su grupo.
            'school_id' => fn (array $attributes) => Classroom::find($attributes['classroom_id'])?->school_id
                ?? SchoolFactory::defaultId(),            
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'birth_date' => fake()->dateTimeBetween('-5 years', '-1 year'),
            'allergies' => fake()->optional(0.2)->randomElement(['Maní', 'Lactosa', 'Huevo']),
        ];
    }
}
