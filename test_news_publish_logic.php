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

// Create a backup of the original state
$originalState = [
    'is_published' => $news->is_published,
    'published_at' => $news->published_at ? $news->published_at->format('Y-m-d H:i:s') : null
];

// Test different scenarios
printHeader("TESTING SCENARIOS");

// Scenario 1: Direct update with is_published = true
echo coloredText("Scenario 1: Direct update with is_published = true\n", "blue");
$news->is_published = true;
$news->save();

// Refresh the article from the database
$news = \App\Models\News::find(1);

echo "After direct update:\n";
echo "is_published (database value): " . ($news->is_published ? 'true' : 'false') . "\n";
echo "isPublished() (method result): " . ($news->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($news->published_at ? $news->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

// Restore original state
$news->is_published = $originalState['is_published'];
if ($originalState['published_at']) {
    $news->published_at = $originalState['published_at'];
} else {
    $news->published_at = null;
}
$news->save();

// Scenario 2: Update with validated data
echo coloredText("\nScenario 2: Update with validated data\n", "blue");
$validated = [
    'title' => $news->title,
    'content' => $news->content,
    'is_published' => true,
    'published_at' => $news->published_at
];
$news->update($validated);

// Refresh the article from the database
$news = \App\Models\News::find(1);

echo "After update with validated data:\n";
echo "is_published (database value): " . ($news->is_published ? 'true' : 'false') . "\n";
echo "isPublished() (method result): " . ($news->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($news->published_at ? $news->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

// Restore original state
$news->is_published = $originalState['is_published'];
if ($originalState['published_at']) {
    $news->published_at = $originalState['published_at'];
} else {
    $news->published_at = null;
}
$news->save();

// Scenario 3: Simulate controller logic
echo coloredText("\nScenario 3: Simulate controller logic\n", "blue");

// Create a mock request with is_published=true
$requestData = [
    'title' => $news->title,
    'content' => $news->content,
    'is_published' => true
];

echo "Mock request data:\n";
print_r($requestData);

// Simulate the controller logic
$newIsPublished = isset($requestData['is_published']) ? (bool)$requestData['is_published'] : false;
echo "newIsPublished: " . ($newIsPublished ? 'true' : 'false') . "\n";

$validated = [
    'title' => $requestData['title'],
    'content' => $requestData['content'],
    'is_published' => $newIsPublished
];

// If is_published is being changed from false to true and published_at is null or in the future,
// set published_at to the current date
if ($newIsPublished && !$news->is_published) {
    echo "is_published is being changed from false to true\n";

    $publishedAt = $news->published_at;
    echo "Current published_at: " . ($publishedAt ? $publishedAt->format('Y-m-d H:i:s') : 'null') . "\n";

    if ($publishedAt === null || $publishedAt > now()) {
        echo "published_at is null or in the future, setting to current date\n";
        $validated['published_at'] = now();
    } else {
        echo "published_at is already set to a past date, not modifying\n";
    }
}

echo "Validated data:\n";
print_r($validated);

$news->update($validated);

// Refresh the article from the database
$news = \App\Models\News::find(1);

echo "After simulating controller logic:\n";
echo "is_published (database value): " . ($news->is_published ? 'true' : 'false') . "\n";
echo "isPublished() (method result): " . ($news->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($news->published_at ? $news->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

// Restore original state
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
