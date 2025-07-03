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
        Schema::create('phones', function (Blueprint $table) {
            $table->id();
            $table->string('number');
            $table->string('country_code');
            $table->timestamps();
        });

        Schema::create('phone_user', function (Blueprint $table) {
            $table->foreignId('phone_id');
            $table->foreignId('user_id');
            $table->primary(['phone_id', 'user_id']);
            $table->timestamp('verified_at')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_user');
        Schema::dropIfExists('phones');
    }
};
