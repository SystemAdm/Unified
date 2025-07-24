<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

// Get a user to test with
$user = User::first();
if (!$user) {
    echo "No users found in the database.\n";
    exit(1);
}

// Get event with ID 61 (as specified in the issue description)
$event = Event::find(61);
if (!$event) {
    echo "Event with ID 61 not found.\n";
    exit(1);
}

// Encode the user ID using base64 (as done in the validateText method)
$encodedUserId = base64_encode($user->id);

echo "Test: Validating encrypted text for event {$event->id} ({$event->title})\n";
echo "User: {$user->id} ({$user->given_name} {$user->family_name})\n";
echo "Encoded User ID: {$encodedUserId}\n\n";

// Check if the user is already registered for the event
$isRegistered = $event->registered->contains($user->id);
echo "User is " . ($isRegistered ? "already" : "not") . " registered for the event.\n";

// If the user is already registered, we'll remove them for testing purposes
if ($isRegistered) {
    $registeredUsers = $event->registered->pluck('id')->toArray();
    $registeredUsers = array_diff($registeredUsers, [$user->id]);
    $event->registered()->sync($registeredUsers);
    $event->refresh();
    echo "Removed user from registered users for testing purposes.\n";
    $isRegistered = $event->registered->contains($user->id);
    echo "User is now " . ($isRegistered ? "still" : "no longer") . " registered for the event.\n\n";
}

// Now we'll simulate the POST request to validate the text
echo "Simulating POST request to validate the text...\n";
echo "POST URL: " . route('admin.events.validate-text.submit', ['event' => $event->id]) . "\n";
echo "POST Data: { 'encrypted_text': '{$encodedUserId}' }\n\n";

// In a real test, we would use Laravel's testing facilities to make the request
// For this script, we'll just call the controller method directly
$controller = new App\Http\Controllers\Admin\EventController();
$request = new Illuminate\Http\Request();
$request->merge(['encrypted_text' => $encodedUserId]);

// We need to be authenticated as an admin to call this method
// This is just a simulation, in a real test we would use Laravel's actingAs method
echo "Note: In a real test, we would authenticate as an admin user.\n";
echo "For this script, we're just simulating the controller method call.\n\n";

try {
    // Call the controller method
    $response = $controller->validateText($event, $request);

    // Parse the response
    $responseData = json_decode($response->getContent(), true);

    echo "Response:\n";
    echo "Success: " . ($responseData['success'] ? "true" : "false") . "\n";
    echo "Message: " . $responseData['message'] . "\n\n";

    // Check if the user is now registered for the event
    $event->refresh();
    $isRegisteredNow = $event->registered->contains($user->id);
    echo "User is now " . ($isRegisteredNow ? "" : "not ") . "registered for the event.\n";

    if ($isRegisteredNow) {
        echo "Test PASSED: User was successfully registered for the event.\n";
    } else {
        echo "Test FAILED: User was not registered for the event.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Test FAILED due to an exception.\n";
}
