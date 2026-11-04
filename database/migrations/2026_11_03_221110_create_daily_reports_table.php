<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('child_id')->constrained()->cascadeOnDelete();
            $table->foreignId('educator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('report_date');
            $table->string('mood')->nullable();
            $table->string('meal_status')->nullable();
            $table->unsignedSmallInteger('nap_minutes')->nullable();
            $table->string('note', 280)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['child_id', 'report_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_reports');
    }
};