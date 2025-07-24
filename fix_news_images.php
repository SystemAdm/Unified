<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get all news articles with invalid image paths
$news = \App\Models\News::where('featured_image', 'news/')->get();

echo "Fixing News Articles with Invalid Image Paths:\n";
echo "===========================================\n";
echo "Found " . $news->count() . " articles with invalid image paths.\n\n";

// Update each article with the valid image path
foreach ($news as $article) {
    echo "Updating ID: {$article->id}, Title: {$article->title}\n";
    echo "Old Featured Image: " . $article->featured_image . "\n";

    // Update to use the existing image file
    $article->featured_image = 'news/4EWPQDydTCBR22wNWUdasX5jnMrAhImnJZSJkxdH.png';
    $article->save();

    echo "New Featured Image: " . $article->featured_image . "\n";
    echo "----------------------------\n";
}

echo "\nDone. All invalid image paths have been fixed.\n";
