<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Phone;
use Illuminate\Support\Facades\Log;

echo "=== Testing User Display in Phone Management ===\n\n";

// Get all users
$users = User::all(['id', 'given_name', 'family_name', 'additional_name']);
echo "Found " . $users->count() . " users in the database.\n\n";

// Display user information
echo "User Information:\n";
echo str_repeat('-', 80) . "\n";
echo sprintf("%-5s | %-20s | %-20s | %-20s | %-30s\n",
    "ID", "Given Name", "Family Name", "Additional Name", "Full Name (computed)");
echo str_repeat('-', 80) . "\n";

foreach ($users as $user) {
    echo sprintf("%-5s | %-20s | %-20s | %-20s | %-30s\n",
        $user->id,
        "'" . ($user->given_name ?? 'null') . "'",
        "'" . ($user->family_name ?? 'null') . "'",
        "'" . ($user->additional_name ?? 'null') . "'",
        "'" . $user->name . "'");
}

echo "\n";

// Check what would be displayed in the UI
echo "UI Display Simulation:\n";
echo str_repeat('-', 80) . "\n";
echo sprintf("%-5s | %-50s\n", "ID", "Display Text in Select Component");
echo str_repeat('-', 80) . "\n";

foreach ($users as $user) {
    $displayText = $user->name ?: "User #{$user->id}";
    echo sprintf("%-5s | %-50s\n", $user->id, "'" . $displayText . "'");
}

echo "\n";

// Check if any phones exist and their associated users
$phones = Phone::with('users')->get();
echo "Found " . $phones->count() . " phones in the database.\n\n";

if ($phones->count() > 0) {
    echo "Phone User Associations:\n";
    echo str_repeat('-', 80) . "\n";
    echo sprintf("%-5s | %-15s | %-5s | %-50s\n",
        "ID", "Phone Number", "UID", "User Display");
    echo str_repeat('-', 80) . "\n";

    foreach ($phones as $phone) {
        if ($phone->users->count() === 0) {
            echo sprintf("%-5s | %-15s | %-5s | %-50s\n",
                $phone->id, $phone->phone_number, "-", "No users associated");
        } else {
            foreach ($phone->users as $user) {
                $displayText = $user->name ?: "User #{$user->id}";
                echo sprintf("%-5s | %-15s | %-5s | %-50s\n",
                    $phone->id, $phone->phone_number, $user->id, "'" . $displayText . "'");
            }
        }
    }
}

echo "\n=== Test completed ===\n";
