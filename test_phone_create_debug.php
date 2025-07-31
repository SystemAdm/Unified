<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Log;

echo "=== Debugging User Names in Phone Creation Page ===\n\n";

// Get users the same way PhoneController does
echo "Method 1: Using User::all(['id', 'name']) (like PhoneController)\n";
$users1 = User::all(['id', 'name']);
echo "Found " . $users1->count() . " users\n\n";

foreach ($users1 as $index => $user) {
    echo "User #{$index} (ID: {$user->id}):\n";
    echo "  name: '" . $user->name . "'\n";
    echo "  name length: " . strlen($user->name) . "\n";

    // Get the raw attributes to see what's actually stored
    $attributes = $user->getAttributes();
    echo "  Raw attributes:\n";
    echo "    id: " . ($attributes['id'] ?? 'null') . "\n";
    echo "    name: '" . ($attributes['name'] ?? 'null') . "'\n";
    echo "    given_name: '" . ($attributes['given_name'] ?? 'null') . "'\n";
    echo "    family_name: '" . ($attributes['family_name'] ?? 'null') . "'\n";
    echo "    additional_name: '" . ($attributes['additional_name'] ?? 'null') . "'\n\n";
}

// Get users with eager loading (like UserController)
echo "\nMethod 2: Using User::with(['roles', 'emails', 'phones']) (like UserController)\n";
$users2 = User::with(['roles', 'emails', 'phones'])->get();
echo "Found " . $users2->count() . " users\n\n";

foreach ($users2 as $index => $user) {
    echo "User #{$index} (ID: {$user->id}):\n";
    echo "  name: '" . $user->name . "'\n";
    echo "  name length: " . strlen($user->name) . "\n";

    // Get the raw attributes to see what's actually stored
    $attributes = $user->getAttributes();
    echo "  Raw attributes:\n";
    echo "    id: " . ($attributes['id'] ?? 'null') . "\n";
    echo "    name: '" . ($attributes['name'] ?? 'null') . "'\n";
    echo "    given_name: '" . ($attributes['given_name'] ?? 'null') . "'\n";
    echo "    family_name: '" . ($attributes['family_name'] ?? 'null') . "'\n";
    echo "    additional_name: '" . ($attributes['additional_name'] ?? 'null') . "'\n\n";
}

// Get users with all attributes
echo "\nMethod 3: Using User::all() (all attributes)\n";
$users3 = User::all();
echo "Found " . $users3->count() . " users\n\n";

foreach ($users3 as $index => $user) {
    echo "User #{$index} (ID: {$user->id}):\n";
    echo "  name: '" . $user->name . "'\n";
    echo "  name length: " . strlen($user->name) . "\n";

    // Get the raw attributes to see what's actually stored
    $attributes = $user->getAttributes();
    echo "  Raw attributes:\n";
    echo "    id: " . ($attributes['id'] ?? 'null') . "\n";
    echo "    name: '" . ($attributes['name'] ?? 'null') . "'\n";
    echo "    given_name: '" . ($attributes['given_name'] ?? 'null') . "'\n";
    echo "    family_name: '" . ($attributes['family_name'] ?? 'null') . "'\n";
    echo "    additional_name: '" . ($attributes['additional_name'] ?? 'null') . "'\n\n";
}

echo "=== Debug complete ===\n";
