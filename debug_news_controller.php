<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\NewsController;
use App\Models\News;
use Illuminate\Support\Facades\Auth;

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
$news = News::find(1);

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

// Create a mock request with is_published=true
printHeader("CREATING MOCK REQUEST");
echo coloredText("Creating a mock request with is_published=true\n", "blue");

// Create a mock request with the minimum required fields
$requestData = [
    'title' => $news->title,
    'content' => $news->content,
    'is_published' => true,
    '_method' => 'PUT'
];

echo "Mock request data:\n";
print_r($requestData);

// Create a Request object
$request = Request::create(
    route('admin.news.update', ['news' => $news->id]),
    'POST',
    $requestData
);

// Log in as an admin user to ensure we have permission to publish
$adminUser = \App\Models\User::whereHas('roles', function($query) {
    $query->where('name', 'admin');
})->first();

if (!$adminUser) {
    echo coloredText("No admin user found. Creating a mock admin user.\n", "yellow");
    // Create a mock admin user if none exists
    $adminUser = new \App\Models\User();
    $adminUser->name = 'Admin User';
    $adminUser->email = 'admin@example.com';
    $adminUser->password = bcrypt('password');
    $adminUser->save();

    // Assign admin role
    $adminUser->assignRole('admin');
}

Auth::login($adminUser);
echo coloredText("Logged in as admin user: {$adminUser->name}\n", "green");

// Create a controller instance
$controller = new NewsController();

// Add debugging to the controller
printHeader("ADDING DEBUGGING");
echo coloredText("Adding debugging to trace the request processing\n", "blue");

// Monkey patch the controller's update method to add debugging
$reflectionMethod = new ReflectionMethod(NewsController::class, 'update');
$startLine = $reflectionMethod->getStartLine();
$endLine = $reflectionMethod->getEndLine();

$file = new SplFileObject(__DIR__ . '/app/Http/Controllers/Admin/NewsController.php');
$lines = [];
$i = 0;
while (!$file->eof()) {
    $lines[] = $file->fgets();
    $i++;
}

echo "Controller update method (lines {$startLine}-{$endLine}):\n";
for ($i = $startLine - 1; $i < $endLine; $i++) {
    echo ($i + 1) . ": " . $lines[$i];
}

// Simulate the controller update method with debugging
printHeader("SIMULATING CONTROLLER UPDATE");
echo coloredText("Simulating the controller update method with debugging\n", "blue");

// Extract the key parts of the update method logic
echo "1. Validating request data\n";
$validated = $request->validate([
    'title' => 'required|string|max:255',
    'excerpt' => 'nullable|string',
    'content' => 'required|string',
    'published_at' => 'nullable|date',
    'is_published' => 'boolean',
    'visible_to_role' => 'nullable|array',
]);

echo "2. Checking if user has admin role\n";
$user = Auth::user();
$hasAdminRole = $user->hasRole(\App\Enum\Role::ADMIN->value);
echo "   User has admin role: " . ($hasAdminRole ? 'true' : 'false') . "\n";

if ($hasAdminRole) {
    echo "3. Processing is_published field\n";
    echo "   Request has is_published: " . ($request->has('is_published') ? 'true' : 'false') . "\n";
    echo "   Request is_published value: " . var_export($request->input('is_published'), true) . "\n";

    // Debug the is_published value in the request
    echo "   Request all data: " . json_encode($request->all()) . "\n";

    // When a checkbox is unchecked, the field might not be included in the request at all
    // or it might be sent as an empty string or null
    // We need to check if the field exists in the request and its value
    if ($request->has('is_published')) {
        $newIsPublished = (bool)$request->input('is_published');
        echo "   Field exists in request, setting newIsPublished to: " . ($newIsPublished ? 'true' : 'false') . "\n";
    } else {
        // If the field doesn't exist in the request, it means the checkbox was unchecked
        $newIsPublished = false;
        echo "   Field doesn't exist in request, setting newIsPublished to: false\n";
    }
    $validated['is_published'] = $newIsPublished;

    echo "4. Checking if is_published is being changed from false to true\n";
    echo "   Current is_published: " . ($news->is_published ? 'true' : 'false') . "\n";
    echo "   New is_published: " . ($newIsPublished ? 'true' : 'false') . "\n";

    // If is_published is being changed from false to true and published_at is null or in the future,
    // set published_at to the current date
    if ($newIsPublished && !$news->is_published) {
        echo "   is_published is being changed from false to true\n";

        $publishedAt = $request->input('published_at');
        echo "   Request published_at: " . ($publishedAt ? $publishedAt : 'null') . "\n";

        if ($publishedAt === null || $publishedAt > now()) {
            echo "   published_at is null or in the future, setting to current date\n";
            $validated['published_at'] = now();
        } else {
            echo "   published_at is already set to a past date, not modifying\n";
        }
    } else {
        echo "   is_published is not being changed from false to true, not modifying published_at\n";
    }
} else {
    echo "3. User is not an admin, setting is_published to false\n";
    $validated['is_published'] = false;
}

// Simulate updating the news article
echo "5. Updating the news article with validated data\n";
echo "   Validated data:\n";
print_r($validated);

// Actually update the article for testing
$originalState = [
    'is_published' => $news->is_published,
    'published_at' => $news->published_at ? $news->published_at->format('Y-m-d H:i:s') : null
];

$news->update($validated);

// Refresh the article from the database
$news = News::find(1);

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
$news = News::find(1);

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
