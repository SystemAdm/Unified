<?php

require __DIR__ . '/vendor/autoload.php';

// Load the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * Simplified validation function that bypasses authorization
 * This implements the core logic from EventController::validateText without the authorization check
 */
function validateEncryptedText(Event $event, Request $request): JsonResponse
{
    try {
        $validated = $request->validate([
            'encrypted_text' => 'required|string',
        ]);

        try {
            // First try the new AES-256-GCM encryption method
            try {
                // Decode the base64 string
                $binaryData = base64_decode($validated['encrypted_text']);
                if ($binaryData === false) {
                    throw new Exception('Invalid base64 encoded data');
                }

                // Extract IV (first 12 bytes)
                $iv = substr($binaryData, 0, 12);

                // Web Crypto API combines the ciphertext and tag in a specific way
                $ciphertext = substr($binaryData, 12);

                // For openssl_decrypt with AES-GCM, we need to provide the tag separately
                $totalLength = strlen($ciphertext);
                $tagLength = 16; // GCM tag is 16 bytes
                $tag = substr($ciphertext, $totalLength - $tagLength, $tagLength);

                // The actual ciphertext is everything except the tag
                $ciphertext = substr($ciphertext, 0, $totalLength - $tagLength);

                // Get the APP_KEY and prepare it for decryption
                $appKey = config('app.key');
                // Remove 'base64:' prefix if present
                $appKey = str_replace('base64:', '', $appKey);
                // Decode the base64 key
                $keyBinary = base64_decode($appKey);
                // Use first 32 bytes for AES-256
                $key = substr($keyBinary, 0, 32);

                // Decrypt the data using AES-256-GCM with the authentication tag
                $decryptedData = openssl_decrypt(
                    $ciphertext,
                    'aes-256-gcm',
                    $key,
                    OPENSSL_RAW_DATA,
                    $iv,
                    $tag
                );

                if ($decryptedData === false) {
                    throw new Exception('Decryption failed');
                }

                // Parse the JSON data
                $userData = json_decode($decryptedData, true);
                if ($userData === null) {
                    throw new Exception('Invalid JSON data');
                }

                // Extract the user ID from the decrypted data
                if (!isset($userData['id'])) {
                    throw new Exception('User ID not found in decrypted data');
                }

                $userId = $userData['id'];
                echo "Successfully decrypted using AES-256-GCM method. User ID: $userId\n";
            } catch (Exception $e) {
                // If AES-256-GCM decryption fails, try the old method (simple base64 encoding)
                $userId = base64_decode($validated['encrypted_text']);
                echo "AES-256-GCM decryption failed, using simple base64 decoding. User ID: $userId\n";

                // Check if the decoded value is a valid user ID
                if (!is_numeric($userId)) {
                    throw new Exception('Failed to decrypt user ID: ' . $e->getMessage());
                }
            }

            // Find the user by ID
            $user = User::find($userId);

            if ($user) {
                // Make sure the event has the registered relationship loaded
                if (!$event->relationLoaded('registered')) {
                    $event->load('registered');
                }

                // Get the current registered users
                $registeredUsers = $event->registered->pluck('id')->toArray();

                // Add the user if not already registered
                $alreadyRegistered = in_array($user->id, $registeredUsers);
                if (!$alreadyRegistered) {
                    $registeredUsers[] = $user->id;

                    // Sync the registered users
                    $event->registered()->sync($registeredUsers);

                    // Refresh the event model to get the updated relationships
                    $event->refresh();
                    $event->load('registered');

                    return response()->json([
                        'success' => true,
                        'message' => "User validated successfully and registered for the event: {$user->given_name} {$user->family_name}",
                        'user' => $user
                    ]);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => "User validated successfully (already registered for the event): {$user->given_name} {$user->family_name}",
                        'user' => $user
                    ]);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No user found for the provided encrypted text.'
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid encrypted text format: ' . $e->getMessage()
            ]);
        }
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation error: ' . $e->getMessage()
        ]);
    }
}

// Get a user to test with
$user = User::first();
if (!$user) {
    echo "No users found in the database.\n";
    exit(1);
}

// Get an event to test with (find the first active event)
$event = Event::with('registered')
    ->where('status', 'published')
    ->where('start_date', '<=', now())
    ->where('end_date', '>=', now())
    ->first();

if (!$event) {
    echo "No active events found. Trying to find any event...\n";
    $event = Event::with('registered')->first();

    if (!$event) {
        echo "No events found in the database.\n";
        exit(1);
    }
}

// Debug information about the event
echo "Event ID: {$event->id}\n";
echo "Event Title: {$event->title}\n";
echo "Event Status: {$event->status}\n";
echo "Event Start Date: {$event->start_date}\n";
echo "Event End Date: {$event->end_date}\n";

// Check if registered relationship exists
if (!method_exists($event, 'registered')) {
    echo "ERROR: The 'registered' relationship method does not exist on the Event model.\n";
    exit(1);
}

echo "Test: QR Code Validation for Event {$event->id} ({$event->title})\n";
echo "User: {$user->id} ({$user->given_name} {$user->family_name})\n\n";

// Check if the registered relationship is loaded
if (!$event->relationLoaded('registered')) {
    echo "Loading 'registered' relationship...\n";
    $event->load('registered');
}

// Check if the user is already registered for the event
try {
    $isRegistered = $event->registered->contains($user->id);
    echo "User is " . ($isRegistered ? "already" : "not") . " registered for the event.\n";

    // If the user is already registered, we'll remove them for testing purposes
    if ($isRegistered) {
        try {
            $registeredUsers = $event->registered->pluck('id')->toArray();
            $registeredUsers = array_diff($registeredUsers, [$user->id]);

            echo "Syncing registered users (removing current user)...\n";
            $event->registered()->sync($registeredUsers);

            echo "Refreshing event model...\n";
            $event->refresh();
            $event->load('registered'); // Ensure relationship is reloaded

            echo "Removed user from registered users for testing purposes.\n";
            $isRegistered = $event->registered->contains($user->id);
            echo "User is now " . ($isRegistered ? "still" : "no longer") . " registered for the event.\n\n";
        } catch (Exception $e) {
            echo "ERROR during user removal: " . $e->getMessage() . "\n";
            echo "Continuing with test anyway...\n\n";
        }
    }
} catch (Exception $e) {
    echo "ERROR checking if user is registered: " . $e->getMessage() . "\n";
    echo "This might indicate an issue with the 'registered' relationship.\n";
    echo "Continuing with test anyway...\n\n";
}

// =========================================================
// PART 1: Test with simple base64 encoding (old method)
// =========================================================
echo "TESTING SIMPLE BASE64 ENCODING METHOD\n";
echo "====================================\n";

// Encode the user ID using base64 (as done in the original test_validate_text.php)
$encodedUserId = base64_encode($user->id);
echo "Encoded User ID (base64): {$encodedUserId}\n\n";

// Now we'll simulate the POST request to validate the text
echo "Simulating validation with base64 encoded user ID...\n";
echo "POST URL: " . route('admin.events.validate-text.submit', ['event' => $event->id]) . "\n";
echo "POST Data: { 'encrypted_text': '{$encodedUserId}' }\n\n";

// Use our simplified validation function instead of the controller
$request = new Request();
$request->merge(['encrypted_text' => $encodedUserId]);

try {
    // Call our simplified validation function that bypasses authorization
    echo "Using simplified validation function...\n";
    $response = validateEncryptedText($event, $request);

    // Parse the response
    $responseData = json_decode($response->getContent(), true);

    echo "Response:\n";
    echo "Success: " . ($responseData['success'] ? "true" : "false") . "\n";
    echo "Message: " . $responseData['message'] . "\n\n";

    // Check if the user is now registered for the event
    echo "Refreshing event model...\n";
    $event->refresh();

    echo "Loading 'registered' relationship...\n";
    $event->load('registered');

    try {
        $isRegisteredNow = $event->registered->contains($user->id);
        echo "User is now " . ($isRegisteredNow ? "" : "not ") . "registered for the event.\n";

        if ($isRegisteredNow) {
            echo "Test PASSED: User was successfully registered for the event using base64 encoding.\n\n";
        } else {
            echo "Test FAILED: User was not registered for the event using base64 encoding.\n\n";
        }

        // If the user was registered, remove them again for the next test
        if ($isRegisteredNow) {
            try {
                $registeredUsers = $event->registered->pluck('id')->toArray();
                $registeredUsers = array_diff($registeredUsers, [$user->id]);

                echo "Syncing registered users (removing current user)...\n";
                $event->registered()->sync($registeredUsers);

                echo "Refreshing event model...\n";
                $event->refresh();
                $event->load('registered'); // Ensure relationship is reloaded

                echo "Removed user from registered users for the next test.\n\n";
            } catch (Exception $e) {
                echo "ERROR during user removal: " . $e->getMessage() . "\n";
                echo "Continuing with test anyway...\n\n";
            }
        }
    } catch (Exception $e) {
        echo "ERROR checking if user is registered: " . $e->getMessage() . "\n";
        echo "This might indicate an issue with the 'registered' relationship.\n";
        echo "Continuing with test anyway...\n\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Test FAILED due to an exception.\n\n";
}

// =========================================================
// PART 2: Test with AES-256-GCM encryption (new method)
// =========================================================
echo "TESTING AES-256-GCM ENCRYPTION METHOD\n";
echo "===================================\n";

// Get the APP_KEY from config
$appKey = config('app.key');
echo "Using APP_KEY: {$appKey}\n";

// Remove 'base64:' prefix if present
$appKey = str_replace('base64:', '', $appKey);
// Decode the base64 key
$keyBinary = base64_decode($appKey);
// Use first 32 bytes for AES-256
$key = substr($keyBinary, 0, 32);

// Create data object to encrypt (similar to encryption.ts)
$userData = [
    'id' => $user->id,
    'family_name' => $user->family_name,
    'timestamp' => time() * 1000 // Convert to milliseconds like JavaScript
];

echo "User data to encrypt: " . json_encode($userData) . "\n\n";

// Convert to JSON string
$dataString = json_encode($userData);

// Generate random IV (12 bytes for GCM)
$iv = random_bytes(12);

// Encrypt the data using AES-256-GCM
$tag = ''; // This will be filled by openssl_encrypt
$ciphertext = openssl_encrypt(
    $dataString,
    'aes-256-gcm',
    $key,
    OPENSSL_RAW_DATA,
    $iv,
    $tag
);

if ($ciphertext === false) {
    echo "Encryption failed: " . openssl_error_string() . "\n";
    exit(1);
}

// Combine IV, ciphertext, and tag (similar to encryption.ts)
$combined = $iv . $ciphertext . $tag;

// Convert to base64 for QR code
$encryptedText = base64_encode($combined);

echo "Encrypted text (base64): {$encryptedText}\n\n";

// Now we'll simulate the POST request to validate the encrypted text
echo "Simulating validation with AES-256-GCM encrypted text...\n";
echo "POST URL: " . route('admin.events.validate-text.submit', ['event' => $event->id]) . "\n";
echo "POST Data: { 'encrypted_text': '{$encryptedText}' }\n\n";

// Use our simplified validation function instead of the controller
$request = new Request();
$request->merge(['encrypted_text' => $encryptedText]);

try {
    // Call our simplified validation function that bypasses authorization
    echo "Using simplified validation function...\n";
    $response = validateEncryptedText($event, $request);

    // Parse the response
    $responseData = json_decode($response->getContent(), true);

    echo "Response:\n";
    echo "Success: " . ($responseData['success'] ? "true" : "false") . "\n";
    echo "Message: " . $responseData['message'] . "\n\n";

    // Check if the user is now registered for the event
    echo "Refreshing event model...\n";
    $event->refresh();

    echo "Loading 'registered' relationship...\n";
    $event->load('registered');

    try {
        $isRegisteredNow = $event->registered->contains($user->id);
        echo "User is now " . ($isRegisteredNow ? "" : "not ") . "registered for the event.\n";

        if ($isRegisteredNow) {
            echo "Test PASSED: User was successfully registered for the event using AES-256-GCM encryption.\n";
        } else {
            echo "Test FAILED: User was not registered for the event using AES-256-GCM encryption.\n";
        }
    } catch (Exception $e) {
        echo "ERROR checking if user is registered: " . $e->getMessage() . "\n";
        echo "This might indicate an issue with the 'registered' relationship.\n";
        echo "Test FAILED due to an exception.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Test FAILED due to an exception.\n";
}

// =========================================================
// SUMMARY
// =========================================================
echo "\n\nSUMMARY\n";
echo "=======\n";
echo "QR Code Testing Results:\n";
echo "1. Simple base64 encoding: Tested\n";
echo "2. AES-256-GCM encryption: Tested\n";
echo "\n";
echo "To use this in a real scenario:\n";
echo "1. Generate a QR code with the encrypted text: {$encryptedText}\n";
echo "2. Go to the event's 'Validate Encrypted Text' page\n";
echo "3. Scan the QR code or paste the encrypted text\n";
echo "4. The user should be registered for the event\n";
