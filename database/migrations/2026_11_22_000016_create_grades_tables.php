<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // RF-07: actividades evaluadas de una materia en un grupo y periodo (ej. "Quiz de fracciones").
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_period_id')->constrained()->restrictOnDelete();
            $table->string('title', 80);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['classroom_id', 'subject_id', 'academic_period_id']);
        });

        // Nota de cada estudiante en cada actividad. La nota del periodo es el promedio.
        Schema::create('assessment_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assessment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('child_id')->constrained()->restrictOnDelete(); // RF-53: los niños no se borran
            $table->decimal('score', 2, 1);
            $table->timestamps();

            $table->unique(['assessment_id', 'child_id']);
        });

        // RF-08: en preescolar, un texto por niño, dimensión y periodo.
        Schema::create('dimension_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->restrictOnDelete();
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('academic_period_id')->constrained()->restrictOnDelete();
            $table->text('body'); // cifrado
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['child_id', 'subject_id', 'academic_period_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dimension_reports');
        Schema::dropIfExists('assessment_scores');
        Schema::dropIfExists('assessments');
    }
};
