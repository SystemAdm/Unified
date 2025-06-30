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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('address');
            $table->timestamps();
        });

        Schema::create('email_user', function (Blueprint $table) {
            $table->foreignId('email_id');
            $table->foreignId('user_id');
            $table->primary(['email_id', 'user_id']);
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
        Schema::dropIfExists('email_user');
        Schema::dropIfExists('emails');
    }
};
