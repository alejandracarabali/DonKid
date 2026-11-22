<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Verificación en dos pasos (TOTP). Obligatoria para el superadministrador.
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('two_factor_secret')->nullable();          // cifrado
            $table->text('two_factor_recovery_codes')->nullable();  // cifrado
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->unsignedBigInteger('two_factor_last_step')->nullable(); // evita reusar un código
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at', 'two_factor_last_step']);
        });
    }
};
