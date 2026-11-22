<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Nivel del grupo (párvulos ... 5.°). Nullable: los grupos existentes
    // quedan sin nivel hasta que coordinación los clasifique.
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->string('level', 20)->nullable()->after('age_range');
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropColumn('level');
        });
    }
};
