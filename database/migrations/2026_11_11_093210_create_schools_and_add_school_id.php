<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $tables = ['users', 'classrooms', 'children', 'announcements'];

    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->string('type', 20)->default('kindergarten');
            $table->timestamps();
        });

        // Los datos que ya existen pasan a un colegio inicial, para no quedar huérfanos.
        $schoolId = DB::table('schools')->insertGetId([
            'name' => 'Colegio Demo',
            'slug' => 'colegio-demo',
            'type' => 'both',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('school_id')->nullable()->constrained()->restrictOnDelete();
            });

            DB::table($name)->update(['school_id' => $schoolId]);
        }
    }

    public function down(): void
    {
        foreach ($this->tables as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('school_id');
            });
        }

        Schema::dropIfExists('schools');
    }
};