<?php

require __DIR__ . '/vendor/autoload.php';

use App\Models\User;
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;

// The validation text from the issue description
$validationText = 'LWg4tCU2gU823YVgiDpPL0atWhc01osOVwPkD3eQln4q5EiEVVDvaSoKf+9o47sSA2hST4q888Fe9e/ydIm/Ka1GVGqmU0Z7wNhjxSMZK1cmn3Ex5A==';

// Find all events to test with
$events = Event::all();
echo "Found " . $events->count() . " events in the database.\n\n";

// Test the validation text against each event
foreach ($events as $event) {
    echo "Testing event {$event->id}: {$event->title}\n";
    echo "----------------------------------------\n";

    // Display event details
    echo "Event details:\n";
    echo "- Seats: " . ($event->seats === null ? "Unlimited" : $event->seats) . "\n";
    echo "- Available seats: " . ($event->available_seats === null ? "Unlimited" : $event->available_seats) . "\n";
    echo "- Min age: " . ($event->min_age === null ? "None" : $event->min_age) . "\n";
    echo "- Max age: " . ($event->max_age === null ? "None" : $event->max_age) . "\n";
    echo "- Restriction: " . ($event->restriction ?: "None") . "\n";
    echo "- Has signup: " . ($event->has_signup ? "Yes" : "No") . "\n";
    if ($event->has_signup) {
        echo "- Signup start: " . ($event->signup_start_date ?: "None") . "\n";
        echo "- Signup end: " . ($event->signup_end_date ?: "None") . "\n";
    }
    echo "- Start date: " . $event->start_date . "\n";
    echo "- End date: " . $event->end_date . "\n\n";

    // Now we'll simulate the POST request to validate the text
    echo "Simulating POST request to validate the text...\n";
    echo "POST URL: " . route('admin.events.validate-text.submit', ['event' => $event->id]) . "\n";
    echo "POST Data: { 'encrypted_text': '{$validationText}' }\n\n";

    // In a real test, we would use Laravel's testing facilities to make the request
    // For this script, we'll just call the controller method directly
    $controller = new App\Http\Controllers\Admin\EventController();
    $request = new Illuminate\Http\Request();
    $request->merge(['encrypted_text' => $validationText]);

    try {
        // Call the controller method
        $response = $controller->validateText($event, $request);

        // Parse the response
        $responseData = json_decode($response->getContent(), true);

        echo "Response:\n";
        echo "Success: " . ($responseData['success'] ? "true" : "false") . "\n";
        echo "Message: " . $responseData['message'] . "\n\n";

        // If we have validation results, display them
        if (isset($responseData['validation_results'])) {
            echo "Validation Results:\n";
            foreach ($responseData['validation_results'] as $result) {
                echo "- {$result['check']}: {$result['status']} - {$result['message']}\n";
            }
            echo "\n";
        }

        // If we have user information, display it
        if (isset($responseData['user'])) {
            $user = $responseData['user'];
            echo "User Information:\n";
            echo "- ID: {$user['id']}\n";
            echo "- Name: {$user['given_name']} {$user['family_name']}\n";
            echo "- Email: {$user['email']}\n";
            echo "- Birthday: " . ($user['birthday'] ?: "Not set") . "\n";
            echo "\n";
        }

        // If this is the event that matches our issue description, mark it
        if (strpos($responseData['message'], "Cannot register for the event: Odd-Erik Jovang") !== false) {
            echo "*** THIS IS THE EVENT FROM THE ISSUE DESCRIPTION ***\n\n";
        }

    } catch (Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }

    echo "----------------------------------------\n\n";
}
