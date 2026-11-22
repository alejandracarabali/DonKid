<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-56 / RF-57: estado y plan de cada colegio, para operar DonKid como suscripción.
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->timestamp('suspended_at')->nullable()->after('renewal_year');
            $table->string('plan', 30)->nullable()->after('suspended_at');
        });
    }

    public function down(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['suspended_at', 'plan']);
        });
    }
};
