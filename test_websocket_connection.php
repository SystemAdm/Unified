<?php

echo "Testing WebSocket Connection Configuration\n";
echo "=========================================\n\n";

// Check if the .env file exists
if (!file_exists(__DIR__ . '/.env')) {
    echo "Error: .env file not found.\n";
    exit(1);
}

// Load environment variables from .env
$env = file_get_contents(__DIR__ . '/.env');
$lines = explode("\n", $env);
$envVars = [];

foreach ($lines as $line) {
    if (strpos($line, '=') !== false && strpos($line, '#') !== 0) {
        list($key, $value) = explode('=', $line, 2);
        $envVars[$key] = $value;
    }
}

// Check Reverb configuration
echo "Reverb Server Configuration:\n";
echo "- REVERB_APP_KEY: " . ($envVars['REVERB_APP_KEY'] ?? 'Not set') . "\n";
echo "- REVERB_HOST: " . ($envVars['REVERB_HOST'] ?? 'Not set') . "\n";
echo "- REVERB_PORT: " . ($envVars['REVERB_PORT'] ?? 'Not set') . "\n";
echo "- REVERB_SCHEME: " . ($envVars['REVERB_SCHEME'] ?? 'Not set') . "\n\n";

// Check Vite Reverb configuration
echo "Frontend WebSocket Configuration:\n";
echo "- VITE_REVERB_APP_KEY: " . ($envVars['VITE_REVERB_APP_KEY'] ?? 'Not set') . "\n";
echo "- VITE_REVERB_HOST: " . ($envVars['VITE_REVERB_HOST'] ?? 'Not set') . "\n";
echo "- VITE_REVERB_PORT: " . ($envVars['VITE_REVERB_PORT'] ?? 'Not set') . "\n";
echo "- VITE_REVERB_SCHEME: " . ($envVars['VITE_REVERB_SCHEME'] ?? 'Not set') . "\n\n";

// Check app.ts configuration
$appTs = file_get_contents(__DIR__ . '/resources/js/app.ts');
echo "app.ts WebSocket Configuration:\n";

if (preg_match('/forceTLS:\s*(.*),/', $appTs, $matches)) {
    echo "- forceTLS: " . trim($matches[1]) . "\n";
} else {
    echo "- forceTLS: Not found\n";
}

if (preg_match('/key:\s*import\.meta\.env\.VITE_REVERB_APP_KEY\s*\|\|\s*[\'"](.*)[\'"]/i', $appTs, $matches)) {
    echo "- Default key: " . $matches[1] . "\n";
}

if (preg_match('/wsHost:\s*import\.meta\.env\.VITE_REVERB_HOST\s*\|\|\s*(.*),/i', $appTs, $matches)) {
    echo "- Default wsHost: " . trim($matches[1]) . "\n";
}

if (preg_match('/wsPort:\s*import\.meta\.env\.VITE_REVERB_PORT\s*\|\|\s*(.*),/i', $appTs, $matches)) {
    echo "- Default wsPort: " . trim($matches[1]) . "\n";
}

echo "\nWebSocket Connection URL that will be used:\n";
$scheme = $envVars['VITE_REVERB_SCHEME'] ?? 'http';
$wsScheme = $scheme === 'https' ? 'wss' : 'ws';
$host = $envVars['VITE_REVERB_HOST'] ?? 'localhost';
$port = $envVars['VITE_REVERB_PORT'] ?? '8080';
$key = $envVars['VITE_REVERB_APP_KEY'] ?? 'herd_key';

echo "- {$wsScheme}://{$host}:{$port}/app/{$key}\n\n";

echo "Configuration Status:\n";
if (
    isset($envVars['VITE_REVERB_APP_KEY']) &&
    isset($envVars['VITE_REVERB_HOST']) &&
    isset($envVars['VITE_REVERB_PORT']) &&
    isset($envVars['VITE_REVERB_SCHEME'])
) {
    echo "✓ All required VITE_ environment variables are set.\n";
} else {
    echo "✗ Some VITE_ environment variables are missing.\n";
}

if (strpos($appTs, 'forceTLS: scheme === \'https\'') !== false) {
    echo "✓ forceTLS is correctly set based on the scheme.\n";
} else {
    echo "✗ forceTLS is not correctly configured.\n";
}

if ($scheme === 'https' && strpos($appTs, 'forceTLS: true') === false && strpos($appTs, 'forceTLS: scheme === \'https\'') === false) {
    echo "✗ Warning: HTTPS scheme is used but forceTLS might not be enabled.\n";
}

echo "\nNote: This script only checks the configuration. To fully test the WebSocket connection,\n";
echo "you would need to run the application and check browser console for connection errors.\n";
