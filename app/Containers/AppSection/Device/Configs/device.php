<?php

return [
    'signature' => [
        // Enable signature verification (still optional unless enforce=true).
        'enabled' => env('DEVICE_SIGNATURE_ENABLED', false),
        // Require signature headers on every request that uses the middleware.
        'enforce' => env('DEVICE_SIGNATURE_ENFORCE', false),
        // Algorithm used for device key signatures.
        'algorithm' => env('DEVICE_SIGNATURE_ALGORITHM', 'ed25519'),
        // Header names
        'header_key_id' => env('DEVICE_SIGNATURE_HEADER_KEY_ID', 'x-key-id'),
        'header_signature' => env('DEVICE_SIGNATURE_HEADER_SIGNATURE', 'x-signature'),
        'header_timestamp' => env('DEVICE_SIGNATURE_HEADER_TIMESTAMP', 'x-timestamp'),
        'header_nonce' => env('DEVICE_SIGNATURE_HEADER_NONCE', 'x-nonce'),
        // TTLs in seconds
        'nonce_ttl' => env('DEVICE_SIGNATURE_NONCE_TTL', 300),
        'timestamp_ttl' => env('DEVICE_SIGNATURE_TIMESTAMP_TTL', 300),
    ],
];
