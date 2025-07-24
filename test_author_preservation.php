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
echo "author_id: " . ($news->author_id ?? 'null') . "\n";

// Create a backup of the original state
$originalState = [
    'is_published' => $news->is_published,
    'published_at' => $news->published_at ? $news->published_at->format('Y-m-d H:i:s') : null,
    'author_id' => $news->author_id
];

// Test 1: Simulate update without our fix
printHeader("TEST 1: SIMULATE UPDATE WITHOUT FIX");

// Create a mock request with is_published=true but no author_id
$requestData = [
    'title' => $news->title,
    'content' => $news->content,
    'is_published' => true
];

echo "Mock request data (note: no author_id):\n";
print_r($requestData);

// Simulate the validation process
$validated = [
    'title' => $requestData['title'],
    'content' => $requestData['content'],
    'is_published' => $requestData['is_published']
];

echo "Validated data without preserved author_id:\n";
print_r($validated);

// Create a clone of the news article to avoid modifying the original
$clonedNews = clone $news;
$clonedNews->fill($validated);

echo "After update without fix:\n";
echo "author_id would be: " . ($clonedNews->author_id ?? 'null') . "\n";

// Test 2: Simulate update with our fix
printHeader("TEST 2: SIMULATE UPDATE WITH FIX");

// Create the same mock request
echo "Mock request data (note: no author_id):\n";
print_r($requestData);

// Simulate the validation process
$validated = [
    'title' => $requestData['title'],
    'content' => $requestData['content'],
    'is_published' => $requestData['is_published']
];

// Apply our fix: preserve the existing author_id
$validated['author_id'] = $news->author_id;

echo "Validated data with preserved author_id:\n";
print_r($validated);

// Update the news article
$news->update($validated);

// Refresh the article from the database
$news = \App\Models\News::find(1);

// Display the updated state
printHeader("UPDATED STATE");
echo "ID: {$news->id}\n";
echo "Title: {$news->title}\n";
echo "is_published (database value): " . ($news->is_published ? 'true' : 'false') . "\n";
echo "isPublished() (method result): " . ($news->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($news->published_at ? $news->published_at->format('Y-m-d H:i:s') : 'null') . "\n";
echo "author_id: " . ($news->author_id ?? 'null') . "\n";

// Check if the update was successful
if ($news->is_published && $news->isPublished() && $news->author_id === $originalState['author_id']) {
    echo coloredText("\nUpdate was successful! The article is now published and the author_id was preserved.\n", "green");
} else {
    echo coloredText("\nUpdate failed! Check the details above to see what went wrong.\n", "red");

    if (!$news->is_published) {
        echo coloredText("- The article is not marked as published in the database.\n", "red");
    }

    if (!$news->isPublished()) {
        echo coloredText("- The isPublished() method returns false.\n", "red");
    }

    if ($news->author_id !== $originalState['author_id']) {
        echo coloredText("- The author_id was not preserved. Original: {$originalState['author_id']}, Current: {$news->author_id}.\n", "red");
    }
}

// Restore original state
printHeader("RESTORING ORIGINAL STATE");
echo coloredText("Restoring the article to its original state...\n", "magenta");

$news->is_published = $originalState['is_published'];
if ($originalState['published_at']) {
    $news->published_at = $originalState['published_at'];
} else {
    $news->published_at = null;
}
$news->author_id = $originalState['author_id'];
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
echo "author_id: " . ($news->author_id ?? 'null') . "\n";

// Provide instructions for testing in the web interface
printHeader("TESTING INSTRUCTIONS");
echo "1. Go to the admin news edit page for article ID 1\n";
echo "2. Check the 'Publish article' checkbox\n";
echo "3. Click 'Update News Article'\n";
echo "4. Check if the article's published status is updated correctly\n";
echo "5. Verify that the author_id is preserved\n";

echo "\nDone.\n";
