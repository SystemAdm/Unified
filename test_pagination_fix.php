<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\News;
use Illuminate\Support\Facades\DB;

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing Pagination Component Fix\n";
echo "==============================\n\n";

// Get paginated news
$news = News::published()
    ->select('id', 'title', 'excerpt', 'content', 'author_id', 'featured_image', 'published_at', 'created_at', 'updated_at')
    ->with('author:id,name')
    ->orderBy('published_at', 'desc')
    ->orderBy('created_at', 'desc')
    ->paginate(12);

// Convert to array to see the structure
$newsArray = $news->toArray();

// Verify the structure matches what our component expects
echo "Verifying pagination structure for LaravelPaginator component:\n";

// Check for links array (for page numbers)
$linksCheck = isset($newsArray['links']) && is_array($newsArray['links']);
echo "1. Has 'links' array for page numbers: " . ($linksCheck ? "YES" : "NO") . "\n";

if ($linksCheck) {
    // Check if links array has items
    echo "   - Number of links: " . count($newsArray['links']) . "\n";

    // Check if links array has page numbers
    $hasPageNumbers = false;
    foreach ($newsArray['links'] as $link) {
        if (isset($link['label']) && is_numeric($link['label'])) {
            $hasPageNumbers = true;
            break;
        }
    }
    echo "   - Contains page numbers: " . ($hasPageNumbers ? "YES" : "NO") . "\n";
}

// Check for navigation URLs
$hasNavUrls = true;
$missingNavUrls = [];

$navUrls = ['first_page_url', 'last_page_url', 'prev_page_url', 'next_page_url'];
foreach ($navUrls as $url) {
    if (!isset($newsArray[$url])) {
        $hasNavUrls = false;
        $missingNavUrls[] = $url;
    }
}

echo "2. Has navigation URLs: " . ($hasNavUrls ? "YES" : "NO") . "\n";
if (!$hasNavUrls) {
    echo "   - Missing URLs: " . implode(', ', $missingNavUrls) . "\n";
}

// Check for next page URL specifically (for Next button)
$hasNextPage = $news->hasMorePages();
$hasNextPageUrl = isset($newsArray['next_page_url']) && $newsArray['next_page_url'] !== null;

echo "3. Has more pages: " . ($hasNextPage ? "YES" : "NO") . "\n";
echo "4. Has next_page_url: " . ($hasNextPageUrl ? "YES" : "NO") . "\n";

if ($hasNextPage !== $hasNextPageUrl) {
    echo "   WARNING: Mismatch between hasMorePages() and next_page_url\n";
}

// Simulate the component's logic
echo "\nSimulating LaravelPaginator component logic:\n";

// For page numbers
$pageNumbers = [];
foreach ($newsArray['links'] as $link) {
    if (isset($link['label']) && is_numeric($link['label'])) {
        $pageNumbers[] = $link['label'];
    }
}

echo "- Page numbers that would be displayed: " . implode(', ', $pageNumbers) . "\n";

// For Next button
$nextButtonEnabled = isset($newsArray['next_page_url']) && $newsArray['next_page_url'] !== null;
echo "- Next button would be " . ($nextButtonEnabled ? "ENABLED" : "DISABLED") . "\n";

// For Previous button
$prevButtonEnabled = isset($newsArray['prev_page_url']) && $newsArray['prev_page_url'] !== null;
echo "- Previous button would be " . ($prevButtonEnabled ? "ENABLED" : "DISABLED") . "\n";

// Summary
echo "\nSummary:\n";
if ($linksCheck && $hasPageNumbers && $hasNavUrls) {
    echo "✓ The pagination structure is compatible with the updated LaravelPaginator component.\n";
    echo "✓ Page numbers should display correctly.\n";

    if ($nextButtonEnabled === $hasNextPage) {
        echo "✓ Next button should be correctly " . ($nextButtonEnabled ? "enabled" : "disabled") . ".\n";
    } else {
        echo "✗ There's a mismatch in the Next button logic.\n";
    }

    if ($prevButtonEnabled === ($news->currentPage() > 1)) {
        echo "✓ Previous button should be correctly " . ($prevButtonEnabled ? "enabled" : "disabled") . ".\n";
    } else {
        echo "✗ There's a mismatch in the Previous button logic.\n";
    }
} else {
    echo "✗ There are still issues with the pagination structure.\n";

    if (!$linksCheck || !$hasPageNumbers) {
        echo "  - Page numbers may not display correctly.\n";
    }

    if (!$hasNavUrls) {
        echo "  - Navigation buttons may not work correctly.\n";
    }
}

echo "\nThe fix should resolve the issues with page numbers not showing and the Next button being incorrectly disabled.\n";
