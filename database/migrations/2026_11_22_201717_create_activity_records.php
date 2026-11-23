<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Autorización de uso de imagen, por niño. Revocable: retirarla vuelve todo a null.
        Schema::table('children', function (Blueprint $table) {
            $table->timestamp('image_consent_at')->nullable();
            $table->foreignId('image_consent_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('image_consent_ip', 45)->nullable(); // 45: cabe una IPv6
        });

        // Registro: la docente marca la actividad como hecha y deja una nota.
        Schema::table('activities', function (Blueprint $table) {
            $table->timestamp('done_at')->nullable();
            $table->foreignId('done_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('done_note')->nullable();
        });

        // Fotos de la actividad, en el disco privado.
        Schema::create('activity_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_photos');

        Schema::table('activities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('done_by');
            $table->dropColumn(['done_at', 'done_note']);
        });

        Schema::table('children', function (Blueprint $table) {
            $table->dropConstrainedForeignId('image_consent_by');
            $table->dropColumn(['image_consent_at', 'image_consent_ip']);
        });
    }
};