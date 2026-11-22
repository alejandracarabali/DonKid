<?php

use App\Models\School;
use App\Support\AcademicDefaults;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-04: materias y dimensiones. RF-05: escala de desempeños.
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete(); // configuración: se va con el colegio
            $table->string('kind', 20); // subject | dimension
            $table->string('name', 80);
            $table->unsignedSmallInteger('position')->default(0);
            $table->boolean('active')->default(true); // se desactiva, no se borra: puede tener notas
            $table->timestamps();

            $table->unique(['school_id', 'kind', 'name']);
        });

        Schema::create('performance_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete(); // configuración: se va con el colegio
            $table->string('name', 30);
            $table->decimal('min', 2, 1);
            $table->decimal('max', 2, 1);
            $table->unsignedTinyInteger('position');
            $table->timestamps();

            $table->unique(['school_id', 'name']);
        });

        // Qué materias se dictan en cada grupo y qué docente la dicta (docente por materia).
        Schema::create('classroom_subject', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('educator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['classroom_id', 'subject_id']);
        });

        // Colegios que ya existen: valores iniciales.
        School::query()->each(fn (School $school) => AcademicDefaults::seed($school));
    }

    public function down(): void
    {
        Schema::dropIfExists('classroom_subject');
        Schema::dropIfExists('performance_levels');
        Schema::dropIfExists('subjects');
    }
};
