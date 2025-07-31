<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "=== Verifying Fix for User Names in Phone Creation Page ===\n\n";

// Simulate the PhoneController's create method after the fix
echo "Using User::all() (like the updated PhoneController)\n";
$users = User::all();
echo "Found " . $users->count() . " users\n\n";

// Check if all users have names
$allUsersHaveNames = true;
$emptyNameCount = 0;

foreach ($users as $index => $user) {
    $hasName = !empty($user->name);
    $allUsersHaveNames = $allUsersHaveNames && $hasName;

    if (!$hasName) {
        $emptyNameCount++;
    }

    echo "User #{$index} (ID: {$user->id}):\n";
    echo "  name: '" . $user->name . "'\n";
    echo "  name length: " . strlen($user->name) . "\n";
    echo "  has name: " . ($hasName ? "Yes" : "No") . "\n";

    // Get the raw attributes to see what's actually stored
    $attributes = $user->getAttributes();
    echo "  Raw attributes:\n";
    echo "    given_name: '" . ($attributes['given_name'] ?? 'null') . "'\n";
    echo "    family_name: '" . ($attributes['family_name'] ?? 'null') . "'\n";
    echo "    additional_name: '" . ($attributes['additional_name'] ?? 'null') . "'\n\n";
}

// Summary
echo "=== Summary ===\n";
echo "Total users: " . $users->count() . "\n";
echo "Users with names: " . ($users->count() - $emptyNameCount) . "\n";
echo "Users without names: " . $emptyNameCount . "\n";
echo "All users have names: " . ($allUsersHaveNames ? "Yes" : "No") . "\n\n";

// Simulate how the Select component would display users
echo "=== Select Component Display Simulation ===\n";
foreach ($users as $index => $user) {
    $displayText = $user->name ?: "User #{$user->id}";
    echo "User #{$index} (ID: {$user->id}): " . $displayText . "\n";
}

echo "\n=== Verification complete ===\n";
