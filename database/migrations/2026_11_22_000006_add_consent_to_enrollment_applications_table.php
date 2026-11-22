<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-37: prueba de la autorización de tratamiento de datos (Ley 1581, art. 9 y 17).
    public function up(): void
    {
        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->timestamp('data_consent_at')->nullable()->after('guardian_address');
            $table->timestamp('health_consent_at')->nullable()->after('data_consent_at');
            $table->string('consent_ip', 45)->nullable()->after('health_consent_at'); // 45: cabe una IPv6
            $table->string('consent_policy_version', 30)->nullable()->after('consent_ip');
        });
    }

    public function down(): void
    {
        Schema::table('enrollment_applications', function (Blueprint $table) {
            $table->dropColumn(['data_consent_at', 'health_consent_at', 'consent_ip', 'consent_policy_version']);
        });
    }
};
