<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "Debugging User Names for Select Component\n";
echo "========================================\n\n";

// Get all users with id and name (same as in PhoneController)
$users = User::all(['id', 'name']);

echo "Total users found: " . $users->count() . "\n\n";

// Display each user's data
foreach ($users as $index => $user) {
    echo "User #{$index} (ID: {$user->id}):\n";
    echo "  name: '" . $user->name . "'\n";
    echo "  name length: " . strlen($user->name) . "\n";

    // Get the raw attributes to see what's actually stored
    $attributes = $user->getAttributes();
    echo "  Raw attributes:\n";
    echo "    given_name: '" . ($attributes['given_name'] ?? 'null') . "'\n";
    echo "    family_name: '" . ($attributes['family_name'] ?? 'null') . "'\n";
    echo "    additional_name: '" . ($attributes['additional_name'] ?? 'null') . "'\n";

    // Check if this user would be displayed in the Select component
    $wouldDisplay = !empty($user->name) ? "Yes" : "No (empty name)";
    echo "  Would display in Select: " . $wouldDisplay . "\n\n";
}

echo "Debug complete.\n";
