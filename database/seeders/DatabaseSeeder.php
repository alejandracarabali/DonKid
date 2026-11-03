<?php

namespace Database\Seeders;

use App\Models\Child;
use App\Models\Classroom;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Una cuenta por rol para probar en local.
        User::factory()->admin()->create([
            'name' => 'Coordinación',
            'email' => 'admin@donkid.test',
        ]);

        User::factory()->educator()->create([
            'name' => 'Laura Educadora',
            'email' => 'laura@donkid.test',
        ]);

        User::factory()->guardian()->create([
            'name' => 'Carlos Papá',
            'email' => 'carlos@donkid.test',
        ]);
                // Grupos de ejemplo y 4 niños en cada uno.
        $grupos = collect(['Caminadores', 'Exploradores'])->map(
            fn ($nombre) => Classroom::factory()->create(['name' => $nombre])
        );

        foreach ($grupos as $grupo) {
            Child::factory()->count(4)->create(['classroom_id' => $grupo->id]);
        }

        // Carlos es acudiente de los dos primeros niños.
        $carlos = User::where('email', 'carlos@donkid.test')->first();
        $carlos->children()->attach(
            Child::take(2)->pluck('id'),
            ['relationship' => 'Papá']
        );
    }
}
