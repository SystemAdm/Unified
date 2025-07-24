<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;

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

// Find news article with ID 1
$news = \App\Models\News::find(1);

if (!$news) {
    echo coloredText("News article with ID 1 does not exist.\n", "red");
    exit;
}

// Display initial state
printHeader("INITIAL STATE");
echo "ID: {$news->id}\n";
echo "Title: {$news->title}\n";
echo "is_published (database value): " . ($news->is_published ? 'true' : 'false') . "\n";
echo "isPublished() (method result): " . ($news->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($news->published_at ? $news->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

// Simulate the publishing process
printHeader("SIMULATING PUBLISHING PROCESS");
echo coloredText("Attempting to publish news article ID 1...\n", "blue");

// Create a backup of the original state
$originalState = [
    'is_published' => $news->is_published,
    'published_at' => $news->published_at ? $news->published_at->format('Y-m-d H:i:s') : null
];

// Simulate the controller logic for publishing
echo "1. Setting is_published to true\n";
$news->is_published = true;

// Check if published_at is null or in the future
if ($news->published_at === null || $news->published_at > now()) {
    echo "2. Setting published_at to current date (was: " .
         ($news->published_at ? $news->published_at->format('Y-m-d H:i:s') : 'null') . ")\n";
    $news->published_at = now();
}

echo "3. Saving changes\n";
$news->save();

// Refresh the article from the database
$news = \App\Models\News::find(1);

// Display the updated state
printHeader("UPDATED STATE");
echo "ID: {$news->id}\n";
echo "Title: {$news->title}\n";
echo "is_published (database value): " . ($news->is_published ? 'true' : 'false') . "\n";
echo "isPublished() (method result): " . ($news->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($news->published_at ? $news->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

// Check if the publishing was successful
if ($news->is_published && $news->isPublished()) {
    echo coloredText("\nPublishing was successful! The article is now published.\n", "green");
} else {
    echo coloredText("\nPublishing failed! The article is still not published.\n", "red");

    // Debug why it failed
    if (!$news->is_published) {
        echo coloredText("  - is_published is still false in the database.\n", "red");
    }

    if (!$news->isPublished()) {
        echo coloredText("  - isPublished() method returns false.\n", "red");

        if ($news->is_published && $news->published_at > now()) {
            echo coloredText("    - published_at is set to a future date: " .
                 $news->published_at->format('Y-m-d H:i:s') . "\n", "red");
        }
    }
}

// Restore the original state
printHeader("RESTORING ORIGINAL STATE");
echo coloredText("Restoring the article to its original state...\n", "magenta");

$news->is_published = $originalState['is_published'];
if ($originalState['published_at']) {
    $news->published_at = $originalState['published_at'];
} else {
    $news->published_at = null;
}
$news->save();

// Refresh the article from the database
$news = \App\Models\News::find(1);

// Display the restored state
printHeader("RESTORED STATE");
echo "ID: {$news->id}\n";
echo "Title: {$news->title}\n";
echo "is_published (database value): " . ($news->is_published ? 'true' : 'false') . "\n";
echo "isPublished() (method result): " . ($news->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($news->published_at ? $news->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

// Provide instructions for testing in the web interface
printHeader("TESTING INSTRUCTIONS");
echo "1. Go to the admin news edit page for article ID 1\n";
echo "2. Check the 'Publish article' checkbox\n";
echo "3. Click 'Update News Article'\n";
echo "4. Check if the article's published status is updated correctly\n";

echo "\nDone.\n";
