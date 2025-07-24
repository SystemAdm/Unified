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

// Find a published news article
$publishedArticle = \App\Models\News::where('is_published', true)->first();

if (!$publishedArticle) {
    echo coloredText("No published articles found. Creating one for testing...\n", "yellow");

    // Create a published article
    $publishedArticle = new \App\Models\News();
    $publishedArticle->title = "Test Published Article";
    $publishedArticle->content = "This is a test article for debugging publishing status.";
    $publishedArticle->is_published = true;
    $publishedArticle->published_at = now();
    $publishedArticle->save();

    echo coloredText("Created test article with ID: {$publishedArticle->id}\n", "green");
} else {
    echo coloredText("Found published article with ID: {$publishedArticle->id}\n", "blue");
}

// Display article details
printHeader("ARTICLE DETAILS");
echo "ID: {$publishedArticle->id}\n";
echo "Title: {$publishedArticle->title}\n";
echo "is_published: " . ($publishedArticle->is_published ? 'true' : 'false') . "\n";
echo "published_at: " . ($publishedArticle->published_at ? $publishedArticle->published_at->format('Y-m-d H:i:s') : 'null') . "\n";
echo "isPublished(): " . ($publishedArticle->isPublished() ? 'true' : 'false') . "\n";

// Simulate the form submission with is_published=false
printHeader("SIMULATING FORM SUBMISSION");
echo coloredText("Simulating form submission with is_published=false\n", "magenta");

// Create a mock request with is_published=false
$mockRequestData = [
    'title' => $publishedArticle->title,
    'content' => $publishedArticle->content,
    'is_published' => false,
    '_method' => 'PUT'
];

echo "Mock request data:\n";
print_r($mockRequestData);

// Log the mock request for comparison with actual requests
Log::info('MOCK REQUEST DATA FOR COMPARISON:', [
    'mock_request' => $mockRequestData,
    'article_id' => $publishedArticle->id
]);

echo coloredText("\nMock request data logged. Please check the Laravel log file.\n", "green");

// Simulate unpublishing the article directly
printHeader("SIMULATING UNPUBLISHING");
echo coloredText("Simulating unpublishing the article directly...\n", "magenta");

// Create a backup of the original state
$originalState = [
    'is_published' => $publishedArticle->is_published,
    'published_at' => $publishedArticle->published_at ? $publishedArticle->published_at->format('Y-m-d H:i:s') : null
];

// Unpublish the article
$publishedArticle->is_published = false;
$publishedArticle->save();

// Refresh the article from the database
$publishedArticle = \App\Models\News::find($publishedArticle->id);

// Display the updated article details
printHeader("UPDATED ARTICLE DETAILS");
echo "ID: {$publishedArticle->id}\n";
echo "Title: {$publishedArticle->title}\n";
echo "is_published: " . ($publishedArticle->is_published ? 'true' : 'false') . "\n";
echo "published_at: " . ($publishedArticle->published_at ? $publishedArticle->published_at->format('Y-m-d H:i:s') : 'null') . "\n";
echo "isPublished(): " . ($publishedArticle->isPublished() ? 'true' : 'false') . "\n";

// Check if the unpublishing was successful
if (!$publishedArticle->is_published && !$publishedArticle->isPublished()) {
    echo coloredText("\nUnpublishing was successful! The article is now unpublished.\n", "green");
} else {
    echo coloredText("\nUnpublishing failed! The article is still published.\n", "red");
}

// Restore the original state
printHeader("RESTORING ORIGINAL STATE");
echo coloredText("Restoring the article to its original state...\n", "magenta");

$publishedArticle->is_published = $originalState['is_published'];
if ($originalState['published_at']) {
    $publishedArticle->published_at = $originalState['published_at'];
}
$publishedArticle->save();

// Refresh the article from the database
$publishedArticle = \App\Models\News::find($publishedArticle->id);

// Display the restored article details
printHeader("RESTORED ARTICLE DETAILS");
echo "ID: {$publishedArticle->id}\n";
echo "Title: {$publishedArticle->title}\n";
echo "is_published: " . ($publishedArticle->is_published ? 'true' : 'false') . "\n";
echo "published_at: " . ($publishedArticle->published_at ? $publishedArticle->published_at->format('Y-m-d H:i:s') : 'null') . "\n";
echo "isPublished(): " . ($publishedArticle->isPublished() ? 'true' : 'false') . "\n";

// Instructions for testing the fix in the web interface
printHeader("TESTING INSTRUCTIONS");
echo "1. Go to the admin news edit page for article ID {$publishedArticle->id}\n";
echo "2. Uncheck the 'Publish article' checkbox\n";
echo "3. Click 'Update News Article'\n";
echo "4. Check if the article's published status is updated correctly\n";
echo "5. Check the Laravel log file for the request data\n";

// Path to the log file
$logPath = storage_path('logs/laravel.log');
echo "\nLog file path: $logPath\n";

echo "\nDone.\n";
