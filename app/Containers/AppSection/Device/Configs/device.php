<?php

return [
    'throttle' => [
        'list_devices' => env('DEVICE_LIST_DEVICES_THROTTLE', '30,1'),
        'list_device_keys' => env('DEVICE_LIST_DEVICE_KEYS_THROTTLE', '30,1'),
        'register' => env('DEVICE_REGISTER_THROTTLE', '20,1'),
        'update' => env('DEVICE_UPDATE_THROTTLE', '30,1'),
        'rotate_key' => env('DEVICE_ROTATE_KEY_THROTTLE', '20,1'),
        'revoke_device' => env('DEVICE_REVOKE_DEVICE_THROTTLE', '20,1'),
        'revoke_key' => env('DEVICE_REVOKE_KEY_THROTTLE', '20,1'),
    ],
    'signature' => [
        // Enable signature verification (still optional unless enforce=true).
        'enabled' => env('DEVICE_SIGNATURE_ENABLED', false),
        // Require signature headers on every request that uses the middleware.
        'enforce' => env('DEVICE_SIGNATURE_ENFORCE', false),
        // Algorithm used for device key signatures.
        'algorithm' => env('DEVICE_SIGNATURE_ALGORITHM', 'ed25519'),
        // Cache key prefix and optional dedicated cache store for signature state.
        'cache_key_prefix' => env('DEVICE_SIGNATURE_CACHE_KEY_PREFIX', 'sig'),
        'cache_store' => env('DEVICE_SIGNATURE_CACHE_STORE', ''),
        // Header names
        'header_key_id' => env('DEVICE_SIGNATURE_HEADER_KEY_ID', 'x-key-id'),
        'header_signature' => env('DEVICE_SIGNATURE_HEADER_SIGNATURE', 'x-signature'),
        'header_timestamp' => env('DEVICE_SIGNATURE_HEADER_TIMESTAMP', 'x-timestamp'),
        'header_nonce' => env('DEVICE_SIGNATURE_HEADER_NONCE', 'x-nonce'),
        // Header validation limits
        'header_limits' => [
            'key_id' => [
                'min' => env('DEVICE_SIGNATURE_HEADER_KEY_ID_MIN', 8),
                'max' => env('DEVICE_SIGNATURE_HEADER_KEY_ID_MAX', 191),
            ],
            'nonce' => [
                'min' => env('DEVICE_SIGNATURE_HEADER_NONCE_MIN', 8),
                'max' => env('DEVICE_SIGNATURE_HEADER_NONCE_MAX', 128),
            ],
            'signature' => [
                'min' => env('DEVICE_SIGNATURE_HEADER_SIGNATURE_MIN', 20),
                'max' => env('DEVICE_SIGNATURE_HEADER_SIGNATURE_MAX', 512),
            ],
        ],
        // TTLs in seconds
        'nonce_ttl' => env('DEVICE_SIGNATURE_NONCE_TTL', 300),
        'timestamp_ttl' => env('DEVICE_SIGNATURE_TIMESTAMP_TTL', 300),
        // Cache active key context by key_id to reduce DB pressure on signed routes.
        'key_context_cache_enabled' => env('DEVICE_SIGNATURE_KEY_CONTEXT_CACHE_ENABLED', true),
        'key_context_cache_ttl' => env('DEVICE_SIGNATURE_KEY_CONTEXT_CACHE_TTL', 60),
        // Optional safety check to prevent stale cached key context from bypassing revocation.
        'key_context_consistency_check' => env('DEVICE_SIGNATURE_KEY_CONTEXT_CONSISTENCY_CHECK', true),
        // Debounce window before writing key/device activity again.
        'activity_touch_debounce_seconds' => env('DEVICE_SIGNATURE_ACTIVITY_TOUCH_DEBOUNCE_SECONDS', 60),
        // Queue tuning for async signature activity updates.
        'activity_queue_connection' => env('DEVICE_SIGNATURE_ACTIVITY_QUEUE_CONNECTION', ''),
        'activity_queue' => env('DEVICE_SIGNATURE_ACTIVITY_QUEUE', 'security'),
        'activity_job_tries' => env('DEVICE_SIGNATURE_ACTIVITY_JOB_TRIES', 3),
        'activity_job_backoff' => env('DEVICE_SIGNATURE_ACTIVITY_JOB_BACKOFF', '5,30,120'),
        'activity_job_timeout' => env('DEVICE_SIGNATURE_ACTIVITY_JOB_TIMEOUT', 15),
        'activity_job_max_exceptions' => env('DEVICE_SIGNATURE_ACTIVITY_JOB_MAX_EXCEPTIONS', 1),
        'activity_job_fail_on_timeout' => env('DEVICE_SIGNATURE_ACTIVITY_JOB_FAIL_ON_TIMEOUT', true),
        // Optional structured logs for the async activity touch job.
        'activity_job_log_enabled' => env('DEVICE_SIGNATURE_ACTIVITY_JOB_LOG_ENABLED', false),
        'activity_job_log_channel' => env('DEVICE_SIGNATURE_ACTIVITY_JOB_LOG_CHANNEL', ''),
        // Optional logs for rejected signature requests.
        'failure_log_enabled' => env('DEVICE_SIGNATURE_FAILURE_LOG_ENABLED', false),
        'failure_log_channel' => env('DEVICE_SIGNATURE_FAILURE_LOG_CHANNEL', ''),
        // Optional throttle for repeated invalid signature attempts (key_id + IP).
        'failure_throttle_enabled' => env('DEVICE_SIGNATURE_FAILURE_THROTTLE_ENABLED', false),
        'failure_throttle_max_attempts' => env('DEVICE_SIGNATURE_FAILURE_THROTTLE_MAX_ATTEMPTS', 20),
        'failure_throttle_decay_seconds' => env('DEVICE_SIGNATURE_FAILURE_THROTTLE_DECAY_SECONDS', 60),
        'failure_throttle_store' => env('DEVICE_SIGNATURE_FAILURE_THROTTLE_STORE', ''),
        'failure_throttle_error_codes' => env(
            'DEVICE_SIGNATURE_FAILURE_THROTTLE_ERROR_CODES',
            'signature_invalid,signature_nonce_reused',
        ),
        // Optional lightweight counters for reject rate / top offenders.
        'metrics_enabled' => env('DEVICE_SIGNATURE_METRICS_ENABLED', false),
        'metrics_store' => env('DEVICE_SIGNATURE_METRICS_STORE', ''),
        'metrics_namespace' => env('DEVICE_SIGNATURE_METRICS_NAMESPACE', 'device_signature'),
        'metrics_ttl_seconds' => env('DEVICE_SIGNATURE_METRICS_TTL_SECONDS', 7200),
    ],
];
