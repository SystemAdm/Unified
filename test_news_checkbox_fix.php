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

// Simulate updating an article with is_published=true
if ($news->count() > 0) {
    // Find an article that is not published
    $article = $news->firstWhere('is_published', false);

    if ($article) {
        echo "\nSimulating update for article ID {$article->id}:\n";
        echo "Before: is_published: " . ($article->is_published ? 'true' : 'false') .
             ", isPublished(): " . ($article->isPublished() ? 'true' : 'false') .
             ", published_at: " . ($article->published_at ? $article->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

        // Simulate the form submission with is_published=true
        $article->is_published = true;
        $article->save();

        // Refresh the article from the database
        $article = \App\Models\News::find($article->id);

        echo "After:  is_published: " . ($article->is_published ? 'true' : 'false') .
             ", isPublished(): " . ($article->isPublished() ? 'true' : 'false') .
             ", published_at: " . ($article->published_at ? $article->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

        // Verify that the article is now published
        if ($article->isPublished()) {
            echo "SUCCESS: Article is now published!\n";
        } else {
            echo "ERROR: Article is still not published despite is_published=true!\n";
            echo "This suggests there might still be an issue with the backend logic.\n";
        }
    } else {
        echo "\nNo unpublished articles found to test with.\n";
    }
}

echo "\nDone.\n";
