<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\News;
use Illuminate\Support\Facades\DB;

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Debugging News Pagination Structure\n";
echo "=================================\n\n";

// Get paginated news
$news = News::published()
    ->select('id', 'title', 'excerpt', 'content', 'author_id', 'featured_image', 'published_at', 'created_at', 'updated_at')
    ->with('author:id,name')
    ->orderBy('published_at', 'desc')
    ->orderBy('created_at', 'desc')
    ->paginate(12);

// Output the structure of the paginator
echo "Paginator Class: " . get_class($news) . "\n\n";

// Check if there are any null items in the data
$hasNullItems = false;
foreach ($news as $index => $item) {
    if ($item === null) {
        echo "NULL ITEM FOUND at index $index\n";
        $hasNullItems = true;
    }
}

if (!$hasNullItems) {
    echo "No null items found in the paginator data.\n";
}

// Check for any items with null id
$hasNullId = false;
foreach ($news as $index => $item) {
    if ($item !== null && $item->id === null) {
        echo "ITEM WITH NULL ID FOUND at index $index\n";
        $hasNullId = true;
    }
}

if (!$hasNullId) {
    echo "No items with null id found in the paginator data.\n";
}

// Output the structure of the data that would be sent to Inertia
$inertiaData = [
    'news' => $news
];

// Convert to JSON and back to simulate Inertia's data transformation
$jsonData = json_encode($inertiaData);
$decodedData = json_decode($jsonData, true);

echo "\nStructure of data sent to Inertia:\n";
print_r($decodedData);

// Check the structure of the 'data' property in the paginator
echo "\nStructure of 'data' property in paginator:\n";
if (isset($decodedData['news']['data'])) {
    // Count total items
    echo "Total items in 'data': " . count($decodedData['news']['data']) . "\n";

    // Check for null items or items with null id
    $nullItems = array_filter($decodedData['news']['data'], function($item) {
        return $item === null;
    });

    $nullIdItems = array_filter($decodedData['news']['data'], function($item) {
        return $item !== null && (!isset($item['id']) || $item['id'] === null);
    });

    echo "Null items in 'data': " . count($nullItems) . "\n";
    echo "Items with null id in 'data': " . count($nullIdItems) . "\n";

    if (count($nullIdItems) > 0) {
        echo "\nFirst item with null id:\n";
        print_r(reset($nullIdItems));
    }
}
