<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-67: la autorización de imagen también se pregunta al inscribir (opcional).
    public function up(): void
    {
        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->timestamp('image_consent_at')->nullable()->after('health_consent_at');
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->dropColumn('image_consent_at');
        });
    }
};