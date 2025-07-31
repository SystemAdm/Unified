<?php

require __DIR__ . '/vendor/autoload.php';

// Bootstrap the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Log;

// Test case 1: User with empty name parts
echo "Test Case 1: User with empty name parts\n";
$user1 = new User();
$user1->given_name = '';
$user1->family_name = '';
$user1->additional_name = '';
echo "Name attribute: '" . $user1->name . "'\n";
echo "Name attribute length: " . strlen($user1->name) . "\n\n";

// Test case 2: User with only spaces in name parts
echo "Test Case 2: User with only spaces in name parts\n";
$user2 = new User();
$user2->given_name = '  ';
$user2->family_name = '  ';
$user2->additional_name = '  ';
echo "Name attribute: '" . $user2->name . "'\n";
echo "Name attribute length: " . strlen($user2->name) . "\n\n";

// Test case 3: User with normal name parts
echo "Test Case 3: User with normal name parts\n";
$user3 = new User();
$user3->given_name = 'John';
$user3->family_name = 'Doe';
echo "Name attribute: '" . $user3->name . "'\n";
echo "Name attribute length: " . strlen($user3->name) . "\n\n";

// Test case 4: User with all name parts
echo "Test Case 4: User with all name parts\n";
$user4 = new User();
$user4->given_name = 'John';
$user4->additional_name = 'William';
$user4->family_name = 'Doe';
echo "Name attribute: '" . $user4->name . "'\n";
echo "Name attribute length: " . strlen($user4->name) . "\n\n";

// Test case 5: User with mixed empty and non-empty name parts
echo "Test Case 5: User with mixed empty and non-empty name parts\n";
$user5 = new User();
$user5->given_name = 'John';
$user5->family_name = '';
echo "Name attribute: '" . $user5->name . "'\n";
echo "Name attribute length: " . strlen($user5->name) . "\n\n";

echo "All tests completed.\n";
