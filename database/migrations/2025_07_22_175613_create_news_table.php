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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_published')->default(false);
            $table->datetime('published_at')->nullable();
            $table->string('title');
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->foreignId('author_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('featured_image')->nullable();
            $table->json('visible_to_role')->nullable(); // Role enum array
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
