<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-36: renovación de matrícula de los estudiantes que continúan.
    public function up(): void
    {
        // Año para el que coordinación abrió la renovación (null = cerrada).
        Schema::table('schools', function (Blueprint $table) {
            $table->unsignedSmallInteger('renewal_year')->nullable()->after('type');
        });

        Schema::create('enrollment_renewals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->foreignId('child_id')->constrained()->restrictOnDelete();
            $table->foreignId('guardian_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('year');
            $table->string('next_level', 20);
            $table->string('guardian_phone', 20);
            $table->text('guardian_address'); // cifrado

            // Prueba de la autorización (RF-37), igual que en la inscripción.
            $table->timestamp('data_consent_at');
            $table->timestamp('health_consent_at')->nullable();
            $table->string('consent_ip', 45);
            $table->string('consent_policy_version', 30);

            $table->timestamps();

            $table->unique(['child_id', 'year']); // una renovación por niño y año
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_renewals');

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn('renewal_year');
        });
    }
};
