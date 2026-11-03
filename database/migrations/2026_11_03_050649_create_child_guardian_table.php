<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('child_guardian', function (Blueprint $table) {
            $table->id();

            // Si se borra el niño o el acudiente, se borra el vínculo (no hay huérfanos).
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Parentesco: dato útil para mostrar ("Mamá", "Abuela").
            $table->string('relationship')->nullable();

            $table->timestamps();

            // Evita vincular dos veces la misma pareja.
            $table->unique(['child_id', 'user_id']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('child_guardian');
    }
};
