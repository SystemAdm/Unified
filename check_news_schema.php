<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Print section header
function printHeader($title) {
    $line = str_repeat('=', strlen($title) + 4);
    echo "\n$line\n  $title  \n$line\n";
}

// Check if the news table exists
if (!Schema::hasTable('news')) {
    echo "The 'news' table does not exist in the database.\n";
    exit;
}

// Get the columns of the news table
$columns = Schema::getColumnListing('news');

printHeader("NEWS TABLE COLUMNS");
echo "Columns in the 'news' table:\n";
foreach ($columns as $column) {
    echo "- $column\n";
}

// Check if the author_id column exists
if (in_array('author_id', $columns)) {
    echo "\nThe 'author_id' column exists in the 'news' table.\n";
} else {
    echo "\nThe 'author_id' column does NOT exist in the 'news' table.\n";
}

// Get the migration status
printHeader("MIGRATION STATUS");
echo "Migrations that have been run:\n";
$migrations = DB::table('migrations')->get();
foreach ($migrations as $migration) {
    echo "- {$migration->migration}\n";
}

// Check if the news table migration has been run
$newsTableMigration = '2025_07_22_175613_create_news_table';
$migrationRun = false;
foreach ($migrations as $migration) {
    if (strpos($migration->migration, $newsTableMigration) !== false) {
        $migrationRun = true;
        break;
    }
}

if ($migrationRun) {
    echo "\nThe news table migration has been run.\n";
} else {
    echo "\nThe news table migration has NOT been run.\n";
}

// Get a sample news article to check its structure
printHeader("SAMPLE NEWS ARTICLE");
$news = \App\Models\News::first();
if ($news) {
    echo "First news article:\n";
    print_r($news->toArray());
} else {
    echo "No news articles found in the database.\n";
}

echo "\nDone.\n";
