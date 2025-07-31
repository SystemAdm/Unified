<?php

require __DIR__ . '/vendor/autoload.php';

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

// Create a simple script to test the validation
$validationText = 'LWg4tCU2gU823YVgiDpPL0atWhc01osOVwPkD3eQln4q5EiEVVDvaSoKf+9o47sSA2hST4q888Fe9e/ydIm/Ka1GVGqmU0Z7wNhjxSMZK1cmn3Ex5A==';

// Create a request object with the validation text
$request = Request::create('/admin/events/1/validate-text', 'POST', [
    'encrypted_text' => $validationText
]);

// Get the controller instance
$controller = app()->make('App\Http\Controllers\Admin\EventController');

// Call the validateText method
$response = $controller->validateText(app()->make('App\Models\Event')->find(1), $request);

// Output the response
echo "Response status: " . $response->getStatusCode() . "\n";
echo "Response content: " . $response->getContent() . "\n";
