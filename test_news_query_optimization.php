<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\News;
use Illuminate\Support\Facades\DB;

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing News Query Optimization\n";
echo "==============================\n\n";

// Function to measure query execution time
function measureQueryTime($callback) {
    // Clear query log
    DB::flushQueryLog();
    DB::enableQueryLog();

    // Measure execution time
    $start = microtime(true);
    $result = $callback();
    $end = microtime(true);

    // Get query log
    $queries = DB::getQueryLog();

    return [
        'time' => ($end - $start) * 1000, // Convert to milliseconds
        'queries' => $queries,
        'result' => $result
    ];
}

// Test 1: Get all published news with pagination
echo "Test 1: Get all published news with pagination\n";
$test1 = measureQueryTime(function() {
    return News::published()
        ->select('id', 'title', 'excerpt', 'content', 'author_id', 'featured_image', 'published_at', 'created_at', 'updated_at')
        ->with('author:id,name')
        ->orderBy('published_at', 'desc')
        ->orderBy('created_at', 'desc')
        ->paginate(12);
});

echo "Execution time: " . number_format($test1['time'], 2) . " ms\n";
echo "Number of queries: " . count($test1['queries']) . "\n";
echo "Number of results: " . $test1['result']->count() . "\n";
echo "Total results: " . $test1['result']->total() . "\n\n";

// Test 2: Get latest news
echo "Test 2: Get latest news\n";
$test2 = measureQueryTime(function() {
    return News::published()
        ->select('id', 'title', 'excerpt', 'content', 'author_id', 'featured_image', 'published_at', 'created_at', 'updated_at')
        ->with('author:id,name')
        ->orderBy('published_at', 'desc')
        ->orderBy('created_at', 'desc')
        ->limit(6)
        ->get();
});

echo "Execution time: " . number_format($test2['time'], 2) . " ms\n";
echo "Number of queries: " . count($test2['queries']) . "\n";
echo "Number of results: " . $test2['result']->count() . "\n\n";

// Test 3: Get a specific news article
echo "Test 3: Get a specific news article\n";
// Get the first news article ID
$firstNewsId = News::published()->first()->id ?? null;

if ($firstNewsId) {
    $test3 = measureQueryTime(function() use ($firstNewsId) {
        return News::published()
            ->select('id', 'title', 'excerpt', 'content', 'author_id', 'featured_image', 'published_at', 'created_at', 'updated_at')
            ->with('author:id,name')
            ->findOrFail($firstNewsId);
    });

    echo "Execution time: " . number_format($test3['time'], 2) . " ms\n";
    echo "Number of queries: " . count($test3['queries']) . "\n";
    echo "Result title: " . $test3['result']->title . "\n\n";
} else {
    echo "No news articles found to test.\n\n";
}

// Print the SQL queries for the first test
echo "SQL Queries for Test 1:\n";
foreach ($test1['queries'] as $index => $query) {
    echo ($index + 1) . ". " . $query['query'] . "\n";
    echo "   Bindings: " . json_encode($query['bindings']) . "\n";
    echo "   Time: " . number_format($query['time'], 2) . " ms\n\n";
}

echo "Optimization Summary:\n";
echo "===================\n";
echo "1. Added pagination to the index method to limit the number of records fetched at once\n";
echo "2. Selected only necessary columns instead of fetching all columns\n";
echo "3. Specified columns to load for the author relationship to reduce data transfer\n";
echo "4. Added indexes on is_published, published_at, and created_at columns for faster filtering and sorting\n";
echo "\nThese optimizations should significantly improve the performance of the news index page,\n";
echo "especially as the number of news articles grows.\n";
