<?php

namespace Database\Factories;

use App\Enums\SchoolType;
use App\Models\School;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<School>
 */
class SchoolFactory extends Factory
{
    /**
     * Colegio de prueba con nombre y slug únicos.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = 'Colegio '.fake()->unique()->lastName();

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'type' => fake()->randomElement(SchoolType::cases()),
        ];
    }

    /**
     * Id del primer colegio (el "Colegio Demo" que crea la migración).
     * Si no existe ninguno, crea uno. Lo usan las demás factories para
     * que, por defecto, todos los datos de prueba queden en el mismo colegio.
     */
    public static function defaultId(): int
    {
        return School::query()->orderBy('id')->value('id')
            ?? School::factory()->create()->id;
    }
}