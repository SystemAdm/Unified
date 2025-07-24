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
        // Drop the old event_user table
        Schema::dropIfExists('event_user');

        // Create new tables for different user roles

        // Event organizers (users)
        Schema::create('event_user', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['event_id', 'user_id']);
        });

        // Event signupped users
        Schema::create('event_signupped_user', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['event_id', 'user_id']);
        });

        // Event registered users
        Schema::create('event_registered_user', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['event_id', 'user_id']);
        });

        // Event attending users
        Schema::create('event_attending_user', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['event_id', 'user_id']);
        });

        // Event visited users
        Schema::create('event_visited_user', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['event_id', 'user_id']);
        });

        // Event inside users
        Schema::create('event_inside_user', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_inside_user');
        Schema::dropIfExists('event_visited_user');
        Schema::dropIfExists('event_attending_user');
        Schema::dropIfExists('event_registered_user');
        Schema::dropIfExists('event_signupped_user');
        Schema::dropIfExists('event_user');

        // Recreate the original event_user table
        Schema::create('event_user', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['organizer', 'signupped', 'registered', 'attending'])->default('organizer');
            $table->timestamps();

            $table->primary(['event_id', 'user_id', 'role']);
        });
    }
};
