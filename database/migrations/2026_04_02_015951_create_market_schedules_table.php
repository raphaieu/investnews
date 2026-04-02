<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('market_schedules', function (Blueprint $table) {
            $table->id();
            $table->date('schedule_date')->unique();
            $table->time('open_time')->default('09:00:00');
            $table->time('close_time')->default('18:00:00');
            $table->enum('market_status', ['open', 'closed', 'half_day'])->default('open');
            $table->string('reason')->nullable();
            $table->string('description')->nullable();
            $table->string('timezone')->default('America/Sao_Paulo');
            $table->boolean('is_dst')->default(false);
            $table->boolean('is_manual')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('market_schedules');
    }
};
