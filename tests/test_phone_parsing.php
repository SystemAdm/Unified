<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Models\Phone;
use Illuminate\Support\Facades\App;

// Bootstrap the application
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Test Norwegian number without country code
$norwegianPhone = new Phone();
try {
    $norwegianPhone->phone_number = '40123456'; // Norwegian mobile number without country code
    echo "Norwegian number without country code parsed successfully.\n";
    echo "Country code: " . $norwegianPhone->country_code . "\n";
    echo "Number: " . $norwegianPhone->number . "\n";
    echo "Full number: " . $norwegianPhone->phone_number . "\n\n";
} catch (Exception $e) {
    echo "Error parsing Norwegian number without country code: " . $e->getMessage() . "\n\n";
}

// Test Norwegian number with country code
$norwegianPhoneWithCode = new Phone();
try {
    $norwegianPhoneWithCode->phone_number = '+4712345678'; // Norwegian number with country code
    echo "Norwegian number with country code parsed successfully.\n";
    echo "Country code: " . $norwegianPhoneWithCode->country_code . "\n";
    echo "Number: " . $norwegianPhoneWithCode->number . "\n";
    echo "Full number: " . $norwegianPhoneWithCode->phone_number . "\n\n";
} catch (Exception $e) {
    echo "Error parsing Norwegian number with country code: " . $e->getMessage() . "\n\n";
}

// Test foreign number without country code (should fail)
$foreignPhone = new Phone();
try {
    $foreignPhone->phone_number = '1234567890'; // US number without country code
    echo "Foreign number without country code parsed successfully (unexpected).\n";
    echo "Country code: " . $foreignPhone->country_code . "\n";
    echo "Number: " . $foreignPhone->number . "\n";
    echo "Full number: " . $foreignPhone->phone_number . "\n\n";
} catch (Exception $e) {
    echo "Error parsing foreign number without country code (expected): " . $e->getMessage() . "\n\n";
}

// Test foreign number with country code
$foreignPhoneWithCode = new Phone();
try {
    $foreignPhoneWithCode->phone_number = '+11234567890'; // US number with country code
    echo "Foreign number with country code parsed successfully.\n";
    echo "Country code: " . $foreignPhoneWithCode->country_code . "\n";
    echo "Number: " . $foreignPhoneWithCode->number . "\n";
    echo "Full number: " . $foreignPhoneWithCode->phone_number . "\n\n";
} catch (Exception $e) {
    echo "Error parsing foreign number with country code: " . $e->getMessage() . "\n\n";
}
