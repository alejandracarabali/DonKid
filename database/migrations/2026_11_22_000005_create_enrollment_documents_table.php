<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-34: documentos de cada solicitud. El archivo vive en el disco PRIVADO
    // (storage/app/private); aquí solo se guarda dónde está y qué es.
    public function up(): void
    {
        Schema::create('enrollment_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_application_id')->constrained()->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('path');
            $table->string('original_name', 200);
            $table->string('mime_type', 100);
            $table->unsignedInteger('size'); // bytes
            $table->timestamps();

            $table->unique(['enrollment_application_id', 'type']); // un archivo por tipo
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_documents');
    }
};
