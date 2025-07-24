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
        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->boolean('activating')->default(false);
            $table->datetime('from_datetime');
            $table->datetime('to_datetime');
            $table->string('title');
            $table->text('description');
            $table->string('type'); // AnnouncementType enum
            $table->json('visible_to_access')->nullable(); // Access enum array
            $table->json('visible_to_role')->nullable(); // Role enum array
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
    }
};
