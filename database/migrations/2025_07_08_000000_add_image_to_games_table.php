<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Note: After running this migration, make sure the storage is linked to the public directory
     * by running the following command:
     * php artisan storage:link
     *
     * This will create a symbolic link from public/storage to storage/app/public
     */
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->string('image')->nullable()->after('console');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
};
