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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();

            // Signup options
            $table->boolean('has_signup')->default(false);
            $table->dateTime('signup_start_date')->nullable();
            $table->dateTime('signup_end_date')->nullable();

            // Number of seats
            $table->integer('seats')->nullable(); // null means unlimited

            // Location
            $table->foreignId('location_id')->nullable()->constrained()->onDelete('set null');

            // Limits
            $table->integer('min_age')->nullable();
            $table->integer('max_age')->nullable();
            $table->string('class_restriction')->nullable();

            // Restriction
            $table->enum('restriction', ['everyone', 'members', 'crew'])->default('everyone');

            // Cancellation
            $table->boolean('is_cancelled')->default(false);
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->string('status')->default('published'); // published, draft, cancelled
            $table->timestamps();
        });

        // Event organizers (users)
        Schema::create('event_user', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['organizer', 'signupped', 'registered', 'attending'])->default('organizer');
            $table->timestamps();

            $table->primary(['event_id', 'user_id', 'role']);
        });

        // Event organizers (organizations)
        Schema::create('event_organization', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->foreignId('organization_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->primary(['event_id', 'organization_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_organization');
        Schema::dropIfExists('event_user');
        Schema::dropIfExists('events');
    }
};
