<?php

namespace Database\Seeders;

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
    }
}
