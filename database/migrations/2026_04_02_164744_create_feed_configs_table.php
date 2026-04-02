<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feed_configs', function (Blueprint $table) {
            $table->id();
            $table->string('feed_id', 32)->unique();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('interval_sec')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feed_configs');
    }
};
