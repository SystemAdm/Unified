<?php

// Test script to check storage directory access

// Check if the storage directory exists
echo "Checking if storage directory exists...\n";
$storageDir = __DIR__ . '/storage';
$storageLinkExists = file_exists($storageDir) && is_dir($storageDir);
echo "Storage directory exists: " . ($storageLinkExists ? "Yes" : "No") . "\n";

if ($storageLinkExists) {
    echo "Storage directory is " . (is_link($storageDir) ? "a symbolic link" : "a regular directory") . "\n";

    // Check if the news directory exists
    $newsDir = $storageDir . '/news';
    $newsDirExists = file_exists($newsDir) && is_dir($newsDir);
    echo "News directory exists: " . ($newsDirExists ? "Yes" : "No") . "\n";

    if ($newsDirExists) {
        // List files in the news directory
        echo "\nFiles in news directory:\n";
        $files = scandir($newsDir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                $filePath = $newsDir . '/' . $file;
                echo "- $file (" . filesize($filePath) . " bytes)\n";

                // Check file permissions
                $perms = fileperms($filePath);
                $info = stat($filePath);
                echo "  Permissions: " . substr(sprintf('%o', $perms), -4) . "\n";
                echo "  Owner: " . $info['uid'] . ", Group: " . $info['gid'] . "\n";

                // Check if file is readable
                echo "  Readable: " . (is_readable($filePath) ? "Yes" : "No") . "\n";
            }
        }
    }

    // Check storage configuration
    echo "\nStorage configuration:\n";

    // Check if we can include Laravel files
    if (file_exists(__DIR__ . '/vendor/autoload.php')) {
        require __DIR__ . '/vendor/autoload.php';

        // Try to bootstrap Laravel
        if (file_exists(__DIR__ . '/bootstrap/app.php')) {
            $app = require_once __DIR__ . '/bootstrap/app.php';
            $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
            $kernel->bootstrap();

            // Get storage configuration
            $publicDiskUrl = config('filesystems.disks.public.url');
            echo "Public disk URL: $publicDiskUrl\n";

            $publicDiskRoot = config('filesystems.disks.public.root');
            echo "Public disk root: $publicDiskRoot\n";

            // Check symbolic links configuration
            $links = config('filesystems.links');
            echo "Symbolic links configuration:\n";
            foreach ($links as $link => $target) {
                echo "- $link => $target\n";
                echo "  Link exists: " . (file_exists($link) ? "Yes" : "No") . "\n";
            }

            // Try to use Storage facade to get URL
            $testImagePath = 'news/4EWPQDydTCBR22wNWUdasX5jnMrAhImnJZSJkxdH.png';
            $storageUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($testImagePath);
            echo "\nStorage URL for test image: $storageUrl\n";

            // Check if the file exists using Storage facade
            $fileExists = \Illuminate\Support\Facades\Storage::disk('public')->exists($testImagePath);
            echo "File exists according to Storage facade: " . ($fileExists ? "Yes" : "No") . "\n";
        } else {
            echo "Could not bootstrap Laravel (bootstrap/app.php not found)\n";
        }
    } else {
        echo "Could not include Laravel files (vendor/autoload.php not found)\n";
    }
} else {
    echo "Storage directory does not exist or is not accessible.\n";
}

echo "\nDone.\n";
