<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/bootstrap/app.php';

use App\Models\News;
use Illuminate\Support\Facades\DB;

echo "===========================================\n";
echo "  TESTING FUTURE PUBLISHED_AT DATE FIX\n";
echo "===========================================\n";

// Find a news article to test with
$article = News::first();
if (!$article) {
    echo "No news articles found. Please create one first.\n";
    exit(1);
}

echo "Original article state:\n";
echo "ID: {$article->id}\n";
echo "Title: {$article->title}\n";
echo "is_published: " . ($article->is_published ? 'true' : 'false') . "\n";
echo "isPublished(): " . ($article->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($article->published_at ? $article->published_at : 'null') . "\n\n";

// Save original state to restore later
$originalIsPublished = $article->is_published;
$originalPublishedAt = $article->published_at;

echo "Setting article to published with future date...\n";
// Set a future date (1 day from now)
$futureDate = now()->addDay();
$article->is_published = true;
$article->published_at = $futureDate;
$article->save();

echo "Article state after setting future date:\n";
echo "is_published: " . ($article->is_published ? 'true' : 'false') . "\n";
echo "isPublished(): " . ($article->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($article->published_at ? $article->published_at : 'null') . "\n\n";

// Simulate the controller logic from our fix
echo "Simulating controller fix logic...\n";
$newIsPublished = true;
$publishedAt = $futureDate;

// This is the fixed logic we implemented
if ($newIsPublished) {
    if ($publishedAt === null || $publishedAt > now()) {
        $article->published_at = now();
        $article->save();
    }
}

echo "Article state after applying fix:\n";
echo "is_published: " . ($article->is_published ? 'true' : 'false') . "\n";
echo "isPublished(): " . ($article->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($article->published_at ? $article->published_at : 'null') . "\n\n";

// Restore original state
echo "Restoring original state...\n";
$article->is_published = $originalIsPublished;
$article->published_at = $originalPublishedAt;
$article->save();

echo "Article state after restoration:\n";
echo "is_published: " . ($article->is_published ? 'true' : 'false') . "\n";
echo "isPublished(): " . ($article->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($article->published_at ? $article->published_at : 'null') . "\n\n";

echo "Test completed.\n";
