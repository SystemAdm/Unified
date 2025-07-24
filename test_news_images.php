<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get all news articles
$news = \App\Models\News::all();

echo "News Articles Featured Images:\n";
echo "=============================\n";
foreach ($news as $article) {
    echo "ID: {$article->id}, Title: {$article->title}\n";
    echo "Featured Image: " . ($article->featured_image ?? 'null') . "\n";

    // Check if the file exists in storage
    if ($article->featured_image) {
        $exists = \Illuminate\Support\Facades\Storage::disk('public')->exists($article->featured_image);
        echo "File exists in storage: " . ($exists ? 'Yes' : 'No') . "\n";
    }

    echo "----------------------------\n";
}

echo "\nDone.\n";
