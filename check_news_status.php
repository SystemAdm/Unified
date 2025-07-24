<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Find news article with ID 1
$news = \App\Models\News::find(1);

if (!$news) {
    echo "News article with ID 1 does not exist.\n";
    exit;
}

echo "News Article ID 1 Details:\n";
echo "Title: {$news->title}\n";
echo "is_published (database value): " . ($news->is_published ? 'true' : 'false') . "\n";
echo "isPublished() (method result): " . ($news->isPublished() ? 'true' : 'false') . "\n";
echo "published_at: " . ($news->published_at ? $news->published_at->format('Y-m-d H:i:s') : 'null') . "\n";

if (!$news->is_published) {
    echo "\nStatus: DRAFT (is_published is false)\n";
} elseif (!$news->isPublished()) {
    echo "\nStatus: SCHEDULED (is_published is true but isPublished() returns false)\n";
    echo "This could be because published_at is set to a future date.\n";
} else {
    echo "\nStatus: PUBLISHED (both is_published and isPublished() are true)\n";
}
