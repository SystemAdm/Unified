<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\News;
use Illuminate\Support\Facades\DB;

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Debugging Laravel Pagination Structure\n";
echo "====================================\n\n";

// Get paginated news
$news = News::published()
    ->select('id', 'title', 'excerpt', 'content', 'author_id', 'featured_image', 'published_at', 'created_at', 'updated_at')
    ->with('author:id,name')
    ->orderBy('published_at', 'desc')
    ->orderBy('created_at', 'desc')
    ->paginate(12);

// Output the structure of the paginator
echo "Paginator Class: " . get_class($news) . "\n\n";

// Convert to array to see the structure
$newsArray = $news->toArray();

// Output the structure of the links property
echo "Structure of 'links' property:\n";
echo "Type: " . gettype($newsArray['links']) . "\n";
if (is_array($newsArray['links'])) {
    echo "Count: " . count($newsArray['links']) . "\n";
    echo "First few items:\n";
    $i = 0;
    foreach ($newsArray['links'] as $link) {
        if ($i++ > 5) break;
        echo "  - " . json_encode($link) . "\n";
    }
}

// Check if there's a separate navigation links property
echo "\nChecking for navigation links property:\n";
if (isset($newsArray['first_page_url'])) {
    echo "first_page_url: " . $newsArray['first_page_url'] . "\n";
}
if (isset($newsArray['last_page_url'])) {
    echo "last_page_url: " . $newsArray['last_page_url'] . "\n";
}
if (isset($newsArray['prev_page_url'])) {
    echo "prev_page_url: " . $newsArray['prev_page_url'] . "\n";
}
if (isset($newsArray['next_page_url'])) {
    echo "next_page_url: " . $newsArray['next_page_url'] . "\n";
}

// Output the complete structure
echo "\nComplete pagination structure:\n";
// Remove data array to keep output manageable
unset($newsArray['data']);
echo json_encode($newsArray, JSON_PRETTY_PRINT) . "\n";

// Check for Laravel's default pagination links
echo "\nChecking Laravel's default pagination links:\n";
echo $news->links()->toHtml();
