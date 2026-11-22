<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-34: solicitudes de inscripción que llegan desde el formulario público.
    // Una solicitud NO es un niño todavía: coordinación la revisa y la aprueba (RF-35).
    public function up(): void
    {
        Schema::create('enrollment_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->string('status', 20)->default('pending')->index();

            // Niño
            $table->string('child_first_name', 60);
            $table->string('child_last_name', 60);
            $table->date('child_birth_date');
            $table->string('desired_level', 20);

            // Acudiente (documento y dirección se guardan cifrados)
            $table->string('guardian_name', 80);
            $table->string('guardian_document_type', 10);
            $table->text('guardian_document_number');
            $table->string('guardian_relationship', 30);
            $table->string('guardian_email', 120);
            $table->string('guardian_phone', 20);
            $table->text('guardian_address');

            // Revisión de coordinación (se usa en el paso 5)
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('rejection_reason', 200)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_applications');
    }
};
