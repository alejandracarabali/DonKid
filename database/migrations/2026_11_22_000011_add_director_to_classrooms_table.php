<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // RF-61: un solo director de grupo por grupo. Una columna (y no una marca en la tabla
    // classroom_user) garantiza que no pueda haber dos directores en el mismo grupo.
    public function up(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->foreignId('director_id')->nullable()->after('level')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('classrooms', function (Blueprint $table) {
            $table->dropConstrainedForeignId('director_id');
        });
    }
};
