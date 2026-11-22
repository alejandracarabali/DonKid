<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Fase 5: solicitudes de la familia (excusa, permiso, recomendación).
    public function up(): void
    {
        Schema::create('family_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->restrictOnDelete();
            $table->foreignId('child_id')->constrained()->restrictOnDelete();
            $table->foreignId('guardian_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 20)->index();
            $table->string('status', 20)->default('sent')->index();

            $table->date('date')->nullable();              // excusa y permiso
            $table->string('time', 5)->nullable();         // hora aproximada (HH:MM)
            $table->string('reason', 40)->nullable();      // motivo de la excusa o tipo de permiso
            $table->text('note')->nullable();              // cifrado: puede hablar de la salud del niño

            // Permiso "recoge otra persona"
            $table->string('pickup_name', 80)->nullable();
            $table->string('pickup_relationship', 30)->nullable();
            $table->string('pickup_phone', 20)->nullable();
            $table->string('pickup_code', 4)->nullable();  // código que la persona dice al recoger

            $table->foreignId('handled_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('handled_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('family_requests');
    }
};
