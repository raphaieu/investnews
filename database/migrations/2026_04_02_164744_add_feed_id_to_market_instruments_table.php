<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('market_instruments', function (Blueprint $table) {
            $table->string('feed_id', 32)->default('mt5-forex')->after('display_name');
            $table->index('feed_id');
        });
    }

    public function down(): void
    {
        Schema::table('market_instruments', function (Blueprint $table) {
            $table->dropIndex(['feed_id']);
            $table->dropColumn('feed_id');
        });
    }
};
