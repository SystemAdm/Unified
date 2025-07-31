<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\News;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing News Pagination Fix\n";
echo "==========================\n\n";

// Simulate the controller's index method
$news = News::published()
    ->select('id', 'title', 'excerpt', 'content', 'author_id', 'featured_image', 'published_at', 'created_at', 'updated_at')
    ->with('author:id,name')
    ->orderBy('published_at', 'desc')
    ->orderBy('created_at', 'desc')
    ->paginate(12);

echo "Paginator structure verification:\n";
echo "- Total news articles: " . $news->total() . "\n";
echo "- Articles per page: " . $news->perPage() . "\n";
echo "- Current page: " . $news->currentPage() . "\n";
echo "- Number of articles on current page: " . count($news->items()) . "\n\n";

// Verify that all articles have an ID
$allHaveIds = true;
foreach ($news->items() as $index => $article) {
    if (!isset($article->id) || $article->id === null) {
        echo "ERROR: Article at index $index is missing an ID\n";
        $allHaveIds = false;
    }
}

if ($allHaveIds) {
    echo "SUCCESS: All articles have valid IDs\n\n";
}

// Verify that the data structure matches what the Vue component expects
echo "Data structure verification:\n";

// Convert to array (similar to what Inertia would do)
$newsArray = $news->toArray();

// Check if the data property exists and is an array
if (isset($newsArray['data']) && is_array($newsArray['data'])) {
    echo "- 'data' property exists and is an array: YES\n";

    // Check if all items in data have an id
    $allDataItemsHaveIds = true;
    foreach ($newsArray['data'] as $index => $item) {
        if (!isset($item['id']) || $item['id'] === null) {
            echo "  ERROR: Item at index $index in data array is missing an ID\n";
            $allDataItemsHaveIds = false;
        }
    }

    if ($allDataItemsHaveIds) {
        echo "- All items in 'data' have valid IDs: YES\n";
    }
} else {
    echo "- 'data' property exists and is an array: NO\n";
}

// Check for other required pagination properties
$requiredProps = ['current_page', 'from', 'last_page', 'links', 'path', 'per_page', 'to', 'total'];
$missingProps = [];

foreach ($requiredProps as $prop) {
    if (!isset($newsArray[$prop])) {
        $missingProps[] = $prop;
    }
}

if (empty($missingProps)) {
    echo "- All required pagination properties exist: YES\n";
} else {
    echo "- All required pagination properties exist: NO\n";
    echo "  Missing properties: " . implode(', ', $missingProps) . "\n";
}

echo "\nConclusion:\n";
if ($allHaveIds && isset($newsArray['data']) && is_array($newsArray['data']) && $allDataItemsHaveIds && empty($missingProps)) {
    echo "The data structure is correct and should work with the updated Vue component.\n";
    echo "The fix for the 'Cannot read properties of null (reading 'id')' error should be successful.\n";
} else {
    echo "There are still issues with the data structure that need to be addressed.\n";
}
