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
        Schema::create('organization_user', function (Blueprint $table) {
            //$table->id();
            $table->foreignId('organization_id')->nullable()->constrained('organizations')->cascadeOnDelete();;;
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->boolean('is_chairman')->default(false)->comment('Is the user the chairman of the organization?');
            $table->boolean('is_board')->default(false)->comment('Is the user a board member of the organization?');
            $table->boolean('is_contact')->default(false)->comment('Is the user a contact of the organization?');
            $table->primary(['organization_id', 'user_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
