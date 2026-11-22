<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-53: un estudiante retirado NO se borra; queda inhabilitado con fecha y motivo.
    public function up(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->date('withdrawn_at')->nullable()->after('allergies');
            $table->string('withdrawal_reason', 200)->nullable()->after('withdrawn_at');
            $table->index('withdrawn_at');
        });
    }

    public function down(): void
    {
        Schema::table('children', function (Blueprint $table) {
            $table->dropIndex(['withdrawn_at']);
            $table->dropColumn(['withdrawn_at', 'withdrawal_reason']);
        });
    }
};
