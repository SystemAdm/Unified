<?php

require __DIR__ . '/vendor/autoload.php';

// Load the .env file
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Bootstrap the application
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Get the ServerStatusService
$serverStatusService = $app->make(App\Services\ServerStatusService::class);

// Test with a known public IP (Google's DNS)
$googleDnsIp = '8.8.8.8';
$googleDnsPort = 53; // DNS port

echo "Testing ping to {$googleDnsIp}...\n";
$isPingable = $serverStatusService->pingServer($googleDnsIp);
echo "Ping result: " . ($isPingable ? "Online" : "Offline") . "\n\n";

echo "Testing port {$googleDnsPort} on {$googleDnsIp}...\n";
$isPortOpen = $serverStatusService->checkPort($googleDnsIp, $googleDnsPort);
echo "Port check result: " . ($isPortOpen ? "Open" : "Closed") . "\n\n";

echo "Getting comprehensive status...\n";
$status = $serverStatusService->getServerStatus($googleDnsIp, $googleDnsPort);
echo "Status: " . json_encode($status, JSON_PRETTY_PRINT) . "\n\n";

// Test with a game server from the database
$gameServer = App\Models\GameServer::first();

if ($gameServer) {
    echo "Testing game server: {$gameServer->name} ({$gameServer->ip_address}:{$gameServer->port})\n";

    $status = $serverStatusService->getServerStatus($gameServer->ip_address, $gameServer->port);
    echo "Status: " . json_encode($status, JSON_PRETTY_PRINT) . "\n\n";

    // Update the game server with the status
    $gameServer->update([
        'is_online' => $status['is_online'],
        'port_open' => $status['port_open'],
        'last_checked_at' => $status['checked_at'],
    ]);

    echo "Game server status updated in database.\n";
} else {
    echo "No game servers found in the database.\n";
}

echo "Test completed.\n";
