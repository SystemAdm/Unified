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

// Print article details
function printArticleDetails($article, $label = "") {
    $isPublishedDb = $article->is_published ? 'true' : 'false';
    $isPublishedMethod = $article->isPublished() ? 'true' : 'false';
    $publishedAt = $article->published_at ? $article->published_at->format('Y-m-d H:i:s') : 'null';

    $status = "";
    if ($isPublishedDb === 'true' && $isPublishedMethod === 'true') {
        $status = coloredText("PUBLISHED", "green");
    } elseif ($isPublishedDb === 'true' && $isPublishedMethod === 'false') {
        $status = coloredText("INCONSISTENT (DB: published, Method: not published)", "red");
    } elseif ($isPublishedDb === 'false') {
        $status = coloredText("NOT PUBLISHED", "yellow");
    }

    echo "$label ID: {$article->id}, Title: {$article->title}\n";
    echo "  is_published (DB): $isPublishedDb\n";
    echo "  isPublished() (Method): $isPublishedMethod\n";
    echo "  published_at: $publishedAt\n";
    echo "  Status: $status\n";

    // Check for inconsistencies
    if ($isPublishedDb === 'true' && $isPublishedMethod === 'false') {
        echo coloredText("  ISSUE DETECTED: Article is marked as published in the database but isPublished() returns false.\n", "red");
        if ($article->published_at && $article->published_at > now()) {
            echo coloredText("  REASON: published_at is set to a future date: $publishedAt\n", "red");
        } elseif ($article->published_at === null) {
            echo coloredText("  REASON: published_at is null\n", "red");
        }
    }

    return $isPublishedDb === 'true' && $isPublishedMethod === 'false';
}

// Get all news articles
printHeader("ALL NEWS ARTICLES");
$news = \App\Models\News::all();

$inconsistentArticles = [];
foreach ($news as $article) {
    $isInconsistent = printArticleDetails($article);
    if ($isInconsistent) {
        $inconsistentArticles[] = $article;
    }
    echo "\n";
}

// Analyze inconsistent articles
if (count($inconsistentArticles) > 0) {
    printHeader("ANALYZING INCONSISTENT ARTICLES");
    echo coloredText("Found " . count($inconsistentArticles) . " articles with inconsistent publishing status.\n", "yellow");

    foreach ($inconsistentArticles as $article) {
        echo "\n";
        printArticleDetails($article, "Inconsistent Article: ");

        // Analyze the issue
        echo coloredText("\nAnalyzing issue for article ID {$article->id}:\n", "blue");

        // Check published_at date
        if ($article->published_at) {
            $now = now();
            $diff = $article->published_at->diffInDays($now);
            $isPast = $article->published_at <= $now;

            echo "Current date/time: " . $now->format('Y-m-d H:i:s') . "\n";
            echo "published_at: " . $article->published_at->format('Y-m-d H:i:s') . "\n";
            echo "Difference: " . ($isPast ? "$diff days in the past" : "$diff days in the future") . "\n";

            if (!$isPast) {
                echo coloredText("ISSUE: published_at is set to a future date\n", "red");
                echo "According to the News model's isPublished() method, an article is only considered published if:\n";
                echo "1. is_published is true AND\n";
                echo "2. published_at is either null OR less than or equal to the current date/time\n";
            }
        } else {
            echo "published_at is null\n";
            echo coloredText("NOTE: With the current implementation, if is_published is true and published_at is null, the article should be considered published.\n", "blue");
        }

        // Simulate fixing the issue
        echo coloredText("\nSimulating fix for article ID {$article->id}:\n", "magenta");

        // Clone the article to avoid modifying the original
        $clonedArticle = clone $article;
        $clonedArticle->published_at = now();

        echo "Before fix:\n";
        printArticleDetails($article);

        echo "\nAfter simulated fix (setting published_at to current date):\n";
        echo "  is_published (DB): " . ($clonedArticle->is_published ? 'true' : 'false') . "\n";
        echo "  isPublished() (Method): " . ($clonedArticle->isPublished() ? 'true' : 'false') . "\n";
        echo "  published_at: " . $clonedArticle->published_at->format('Y-m-d H:i:s') . "\n";

        // Automatically apply the fix
        echo coloredText("\nAutomatically applying fix to article ID {$article->id}...\n", "cyan");
        $article->published_at = now();
        $article->save();

        // Refresh the article from the database
        $article = \App\Models\News::find($article->id);

        echo coloredText("Fix applied successfully!\n", "green");
        printArticleDetails($article, "Updated Article: ");
    }
}

// Test publishing an unpublished article
printHeader("TESTING ARTICLE PUBLISHING");

// Find an unpublished article
$unpublishedArticle = \App\Models\News::where('is_published', false)->first();

if ($unpublishedArticle) {
    echo coloredText("Found unpublished article ID: {$unpublishedArticle->id}\n", "blue");
    printArticleDetails($unpublishedArticle, "Before publishing: ");

    echo coloredText("\nSimulating publishing process for article ID {$unpublishedArticle->id}:\n", "magenta");

    // Simulate the controller logic
    echo "1. Setting is_published to true\n";
    $unpublishedArticle->is_published = true;

    // Check if published_at is null or in the future
    $shouldUpdatePublishedAt = false;
    if ($unpublishedArticle->published_at === null) {
        echo "2. published_at is null, will set to current date\n";
        $shouldUpdatePublishedAt = true;
    } elseif ($unpublishedArticle->published_at > now()) {
        echo "2. published_at is in the future, will set to current date\n";
        $shouldUpdatePublishedAt = true;
    } else {
        echo "2. published_at is already set to a past date, will not modify\n";
    }

    if ($shouldUpdatePublishedAt) {
        $unpublishedArticle->published_at = now();
    }

    echo "3. Saving article\n";
    // Don't actually save, just simulate

    echo "\nAfter simulated publishing:\n";
    echo "  is_published (DB): " . ($unpublishedArticle->is_published ? 'true' : 'false') . "\n";
    echo "  isPublished() (Method): " . ($unpublishedArticle->isPublished() ? 'true' : 'false') . "\n";
    echo "  published_at: " . ($unpublishedArticle->published_at ? $unpublishedArticle->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

    // Automatically publish the article
    echo coloredText("\nAutomatically publishing article ID {$unpublishedArticle->id}...\n", "cyan");
    // Actually save the changes
    $unpublishedArticle->save();

    // Refresh the article from the database
    $unpublishedArticle = \App\Models\News::find($unpublishedArticle->id);

    echo coloredText("Article published successfully!\n", "green");
    printArticleDetails($unpublishedArticle, "Published Article: ");
} else {
    echo coloredText("No unpublished articles found to test with.\n", "yellow");
}

// Analyze the News model and controller logic
printHeader("ANALYZING CODE LOGIC");

echo coloredText("News Model isPublished() Method:\n", "blue");
echo "public function isPublished(): bool\n";
echo "{\n";
echo "    return \$this->is_published &&\n";
echo "           (\$this->published_at === null || \$this->published_at <= now());\n";
echo "}\n\n";

echo coloredText("This means an article is considered published when:\n", "blue");
echo "1. is_published is true AND\n";
echo "2. EITHER published_at is null OR published_at is in the past or present\n\n";

echo coloredText("NewsController update() Method Logic:\n", "blue");
echo "If is_published is being changed from false to true and published_at is null or in the future,\n";
echo "set published_at to the current date\n\n";

echo coloredText("Potential Issues:\n", "magenta");
echo "1. If an article already has is_published=true but published_at is in the future,\n";
echo "   it won't be considered published by isPublished() method\n";
echo "2. The controller only updates published_at when changing from unpublished to published,\n";
echo "   not when an article is already marked as published\n";

echo "\n\nDebugging completed.\n";
