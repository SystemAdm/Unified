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
        Schema::table('game_servers', function (Blueprint $table) {
            $table->boolean('is_online')->nullable();
            $table->boolean('port_open')->nullable();
            $table->timestamp('last_checked_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_servers', function (Blueprint $table) {
            $table->dropColumn('is_online');
            $table->dropColumn('port_open');
            $table->dropColumn('last_checked_at');
        });
    }
};
