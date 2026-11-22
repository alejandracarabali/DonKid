<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-02: año escolar y sus periodos académicos (3, según el SRS).
    public function up(): void
    {
        Schema::create('school_years', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->unique(['school_id', 'year']);
        });

        Schema::create('academic_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_year_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('number'); // 1, 2, 3
            $table->date('starts_on');
            $table->date('ends_on');
            $table->date('grades_due_on');          // fecha límite para registrar notas
            $table->timestamp('closed_at')->nullable(); // RF-13: periodo cerrado, notas bloqueadas
            $table->timestamps();

            $table->unique(['school_year_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_periods');
        Schema::dropIfExists('school_years');
    }
};
