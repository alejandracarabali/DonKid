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
        Schema::create('children', function (Blueprint $table) {
            $table->id();

            // Llave foránea: cada niño pertenece a un grupo.
            // constrained() la apunta a la tabla "classrooms".
            // nullOnDelete(): si se borra el grupo, el niño NO se borra;
            // queda "sin grupo" hasta que lo reasignen.
            $table->foreignId('classroom_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');
            $table->text('allergies')->nullable();   // dato sensible: solo lo ven roles autorizados
            $table->timestamps();

            // Índice para la consulta frecuente: "niños de este grupo".
            // (foreignId ya crea uno, pero conviene saber que existe.)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('children');
    }
};
