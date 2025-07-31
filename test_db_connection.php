<?php

require __DIR__ . '/vendor/autoload.php';

// Load the Laravel application
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Testing database connection...\n";

try {
    // Test database connection
    $connection = DB::connection();
    echo "Database connection successful!\n";
    echo "Connection name: " . $connection->getName() . "\n";
    echo "Connection driver: " . $connection->getDriverName() . "\n";

    // Get database path for SQLite
    if ($connection->getDriverName() === 'sqlite') {
        echo "SQLite database path: " . $connection->getDatabaseName() . "\n";

        // Check if the file exists and is readable
        $dbPath = $connection->getDatabaseName();
        if (file_exists($dbPath)) {
            echo "Database file exists.\n";
            if (is_readable($dbPath)) {
                echo "Database file is readable.\n";
            } else {
                echo "WARNING: Database file is not readable!\n";
            }
            if (is_writable($dbPath)) {
                echo "Database file is writable.\n";
            } else {
                echo "WARNING: Database file is not writable!\n";
            }
            echo "Database file size: " . filesize($dbPath) . " bytes\n";
        } else {
            echo "ERROR: Database file does not exist at path: $dbPath\n";
        }
    }

    // Test a simple query
    echo "\nTesting a simple query...\n";
    $users = DB::table('users')->count();
    echo "Number of users in the database: $users\n";

    // Test a query on the events table
    echo "\nTesting query on events table...\n";
    $events = DB::table('events')->count();
    echo "Number of events in the database: $events\n";

} catch (\Exception $e) {
    echo "ERROR: Database connection failed!\n";
    echo "Error message: " . $e->getMessage() . "\n";
    echo "Error code: " . $e->getCode() . "\n";
    echo "Error file: " . $e->getFile() . "\n";
    echo "Error line: " . $e->getLine() . "\n";
    echo "Error trace:\n" . $e->getTraceAsString() . "\n";
}
