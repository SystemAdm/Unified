<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get all news articles
$news = \App\Models\News::all();

echo "All News Articles:\n";
echo "=================\n";
foreach ($news as $article) {
    echo "ID: {$article->id}, Title: {$article->title}, is_published: " .
         ($article->is_published ? 'true' : 'false') .
         ", isPublished(): " . ($article->isPublished() ? 'true' : 'false') .
         ", published_at: " . ($article->published_at ? $article->published_at->format('Y-m-d H:i:s') : 'null') . "\n";
}

// If there are any news articles, let's check the first one in detail
if ($news->count() > 0) {
    $firstArticle = $news->first();

    echo "\nDetailed information for article ID {$firstArticle->id}:\n";
    echo "Raw database value for is_published: " .
         var_export(\DB::table('news')->where('id', $firstArticle->id)->value('is_published'), true) . "\n";

    // Check the actual database column type
    $columnType = \DB::connection()->getDoctrineColumn('news', 'is_published')->getType()->getName();
    echo "Database column type for is_published: {$columnType}\n";
}

echo "\nDone.\n";
