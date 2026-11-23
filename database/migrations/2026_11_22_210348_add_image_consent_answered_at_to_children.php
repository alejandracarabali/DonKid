<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Cuándo respondió la familia (sí o no). Mientras sea null, el Inicio le pregunta.
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->timestamp('image_consent_answered_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropColumn('image_consent_answered_at');
        });
    }
};