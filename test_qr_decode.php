<?php

// The QR code text from the issue description
$qrCodeText = 'pqTKTBJFX5/CsvI9p2+bJzp4ocY7kWp1Jh25kd24vANF3LvJuNW7plZJii0atuN4eiA3t0SlbXh9r2XjWa+MT1OfT9dlNCzEXQ5BH+Lt9QGREy2v7Q==';

echo "QR Code Text: " . $qrCodeText . "\n\n";

// Try simple base64 decoding (current implementation)
try {
    $decodedText = base64_decode($qrCodeText, true);

    if ($decodedText === false) {
        echo "Base64 decoding failed: The input is not valid base64 encoded data.\n";
    } else {
        echo "Base64 decoding succeeded.\n";
        echo "Decoded text (binary representation): " . bin2hex($decodedText) . "\n";

        // Check if the decoded text is numeric (could be a user ID)
        if (is_numeric($decodedText)) {
            echo "Decoded text is numeric: " . $decodedText . " (could be a user ID)\n";
        } else {
            echo "Decoded text is not numeric.\n";

            // Check if it might be JSON
            $jsonData = json_decode($decodedText, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                echo "Decoded text is valid JSON: " . print_r($jsonData, true) . "\n";
            } else {
                echo "Decoded text is not valid JSON.\n";
            }
        }
    }
} catch (Exception $e) {
    echo "Exception during base64 decoding: " . $e->getMessage() . "\n";
}

// Let's try to see if it's encrypted with something else before base64
echo "\nTrying other common decryption methods...\n";

// Check if it might be encrypted with OpenSSL
// This is just a test with a common key and method, real implementation would need the correct key
$methods = [
    'aes-128-cbc',
    'aes-256-cbc',
    'aes-128-ecb',
    'aes-256-ecb'
];

$commonKeys = [
    'secret',
    'laravel',
    'app',
    'key'
];

foreach ($methods as $method) {
    foreach ($commonKeys as $key) {
        echo "\nTrying $method with key '$key':\n";
        try {
            $iv = substr(hash('sha256', 'iv'), 0, 16);
            $key = substr(hash('sha256', $key), 0, 32);

            $decrypted = openssl_decrypt(
                base64_decode($qrCodeText),
                $method,
                $key,
                0,
                $method === 'aes-128-ecb' || $method === 'aes-256-ecb' ? '' : $iv
            );

            if ($decrypted !== false) {
                echo "Decryption succeeded: $decrypted\n";

                // Check if the decrypted text is numeric (could be a user ID)
                if (is_numeric($decrypted)) {
                    echo "Decrypted text is numeric: $decrypted (could be a user ID)\n";
                }

                // Check if it might be JSON
                $jsonData = json_decode($decrypted, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    echo "Decrypted text is valid JSON: " . print_r($jsonData, true) . "\n";
                }
            } else {
                echo "Decryption failed.\n";
            }
        } catch (Exception $e) {
            echo "Exception during decryption: " . $e->getMessage() . "\n";
        }
    }
}

// Let's also check if Laravel's encryption is being used
echo "\nNote: This script cannot test Laravel's encryption directly as it requires the app key.\n";
echo "To test Laravel's encryption, you would need to run this within the Laravel application context.\n";
