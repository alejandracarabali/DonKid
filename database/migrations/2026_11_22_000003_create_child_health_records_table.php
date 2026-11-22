<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-38: ficha de salud. Tabla aparte de "children" porque son datos sensibles
    // (Ley 1581): se cifran y se consultan solo cuando hacen falta.
    public function up(): void
    {
        Schema::create('child_health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->unique()->constrained()->restrictOnDelete();
            $table->string('eps', 80)->nullable();
            $table->text('conditions')->nullable();   // cifrado (casts 'encrypted')
            $table->text('medications')->nullable();  // cifrado (casts 'encrypted')
            $table->string('emergency_contact_name', 80)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('child_health_records');
    }
};
