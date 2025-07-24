<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find a news article that has is_published=true but isPublished()=false
$problematicArticles = \App\Models\News::where('is_published', true)
    ->where(function($query) {
        $query->whereNull('published_at')
            ->orWhere('published_at', '>', now());
    })
    ->get();

echo "Found " . $problematicArticles->count() . " problematic articles (is_published=true but future published_at):\n";
foreach ($problematicArticles as $article) {
    echo "ID: {$article->id}, Title: {$article->title}, is_published: " .
         ($article->is_published ? 'true' : 'false') .
         ", isPublished(): " . ($article->isPublished() ? 'true' : 'false') .
         ", published_at: " . ($article->published_at ? $article->published_at->format('Y-m-d H:i:s') : 'null') . "\n";
}

// Simulate the fix for one of these articles
if ($problematicArticles->count() > 0) {
    $article = $problematicArticles->first();

    echo "\nSimulating fix for article ID {$article->id}:\n";
    echo "Before: is_published: " . ($article->is_published ? 'true' : 'false') .
         ", isPublished(): " . ($article->isPublished() ? 'true' : 'false') .
         ", published_at: " . ($article->published_at ? $article->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

    // Apply our fix logic
    if ($article->is_published && (!$article->published_at || $article->published_at > now())) {
        $article->published_at = now();
        $article->save();

        // Refresh the article from the database
        $article = \App\Models\News::find($article->id);

        echo "After:  is_published: " . ($article->is_published ? 'true' : 'false') .
             ", isPublished(): " . ($article->isPublished() ? 'true' : 'false') .
             ", published_at: " . ($article->published_at ? $article->published_at->format('Y-m-d H:i:s') : 'null') . "\n";
    }
}

echo "\nDone.\n";
