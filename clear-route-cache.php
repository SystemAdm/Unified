<?php

// Path to the route cache file
$routeCacheFile = __DIR__ . '/bootstrap/cache/routes-v7.php';

// Check if the file exists
if (file_exists($routeCacheFile)) {
    // Delete the file
    unlink($routeCacheFile);
    echo "Route cache cleared successfully.\n";
} else {
    echo "Route cache file not found.\n";
}

// Check for other route cache files
$cacheDir = __DIR__ . '/bootstrap/cache/';
$files = scandir($cacheDir);
foreach ($files as $file) {
    if (strpos($file, 'routes-') === 0 && $file !== '.gitignore') {
        unlink($cacheDir . $file);
        echo "Additional route cache file {$file} cleared.\n";
    }
}

echo "Done.\n";
