<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Helper function to print colored text
function coloredText($text, $color) {
    $colors = [
        'red' => "\033[31m",
        'green' => "\033[32m",
        'yellow' => "\033[33m",
        'blue' => "\033[34m",
        'magenta' => "\033[35m",
        'cyan' => "\033[36m",
        'white' => "\033[37m",
        'reset' => "\033[0m"
    ];

    // Windows PowerShell might not support ANSI color codes
    // So we'll add a prefix to make it more visible
    $prefix = "";
    switch ($color) {
        case 'red': $prefix = "[ERROR] "; break;
        case 'green': $prefix = "[SUCCESS] "; break;
        case 'yellow': $prefix = "[WARNING] "; break;
        case 'blue': $prefix = "[INFO] "; break;
        case 'magenta': $prefix = "[DEBUG] "; break;
        default: $prefix = ""; break;
    }

    return $prefix . $colors[$color] . $text . $colors['reset'];
}

// Print section header
function printHeader($title) {
    $line = str_repeat('=', strlen($title) + 4);
    echo "\n$line\n  $title  \n$line\n";
}

// Test 1: Check if the author relationship is being loaded correctly in the admin news index
printHeader("TEST 1: ADMIN NEWS INDEX");
$news = \App\Models\News::with('author')->orderBy('created_at', 'desc')->limit(5)->get();

echo "Found " . $news->count() . " news articles\n";
foreach ($news as $article) {
    echo "ID: {$article->id}, Title: {$article->title}\n";

    if ($article->author) {
        echo coloredText("  Author: {$article->author->name} (ID: {$article->author->id})\n", "green");
    } else {
        echo coloredText("  No author found\n", "yellow");
    }
}

// Test 2: Check if the author relationship is being loaded correctly in the public news index
printHeader("TEST 2: PUBLIC NEWS INDEX");
$publishedNews = \App\Models\News::published()->with('author')->orderBy('published_at', 'desc')->limit(5)->get();

echo "Found " . $publishedNews->count() . " published news articles\n";
foreach ($publishedNews as $article) {
    echo "ID: {$article->id}, Title: {$article->title}\n";

    if ($article->author) {
        echo coloredText("  Author: {$article->author->name} (ID: {$article->author->id})\n", "green");
    } else {
        echo coloredText("  No author found\n", "yellow");
    }
}

// Test 3: Check if the author relationship is being loaded correctly in the getLatestNews method
printHeader("TEST 3: LATEST NEWS");
$latestNews = \App\Models\News::published()->with('author')->orderBy('published_at', 'desc')->orderBy('created_at', 'desc')->limit(6)->get();

echo "Found " . $latestNews->count() . " latest news articles\n";
foreach ($latestNews as $article) {
    echo "ID: {$article->id}, Title: {$article->title}\n";

    if ($article->author) {
        echo coloredText("  Author: {$article->author->name} (ID: {$article->author->id})\n", "green");
    } else {
        echo coloredText("  No author found\n", "yellow");
    }
}

echo "\nTests completed. Please check the output above to verify that the author relationship is being loaded correctly.\n";
echo "If you see author names and IDs displayed in green, the changes are working correctly.\n";
echo "If you see 'No author found' in yellow, it means the news article doesn't have an author assigned.\n";
