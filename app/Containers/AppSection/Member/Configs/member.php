<?php

return [
    'auth' => [
        'login_enabled' => env('MEMBER_LOGIN_ENABLED', true),
        'registration_enabled' => env('MEMBER_REGISTRATION_ENABLED', true),
    ],
    'email_verification' => [
        'enabled' => env('MEMBER_EMAIL_VERIFICATION', true),
    ],
    'password_reset' => [
        'enabled' => env('MEMBER_PASSWORD_RESET', true),
        'expire' => env('MEMBER_PASSWORD_RESET_EXPIRE', 60),
        // Optional frontend reset URL. If set, token + email will be appended as query params.
        'url' => env('MEMBER_PASSWORD_RESET_URL'),
    ],
    'email' => [
        'templates' => [
            'confirm-email' => [
                'subject' => 'Confirm your email',
                'variables' => [
                    'verify_link' => 'Confirmation link',
                    'member_name' => 'Member name',
                ],
            ],
            'password-reminder' => [
                'subject' => 'Reset your password',
                'variables' => [
                    'reset_link' => 'Password reset link',
                    'token' => 'Reset token',
                ],
            ],
        ],
    ],
    'activity_log' => [
        'retention_days' => env('MEMBER_ACTIVITY_LOG_RETENTION_DAYS', 30),
    ],
    'csrf' => [
        'enabled' => env('MEMBER_CSRF_ENABLED', true),
        'cookie_name' => env('MEMBER_CSRF_COOKIE_NAME', 'memberCsrfToken'),
        'header_name' => env('MEMBER_CSRF_HEADER_NAME', 'x-csrf-token'),
    ],
    'phone' => [
        'default_country_code' => env('MEMBER_DEFAULT_COUNTRY_CODE', '84'),
    ],
    'throttle' => [
        'login' => env('MEMBER_LOGIN_THROTTLE', '6,1'),
        'register' => env('MEMBER_REGISTER_THROTTLE', '6,1'),
        'password_reset' => env('MEMBER_PASSWORD_RESET_THROTTLE', '6,1'),
        'refresh' => env('MEMBER_REFRESH_THROTTLE', '12,1'),
    ],
    'social' => [
        'one_time_token_ttl' => env('MEMBER_SOCIAL_TOKEN_TTL', 1),
        'web_redirect_url' => env('MEMBER_SOCIAL_WEB_REDIRECT_URL'),
        'google' => [
            'enabled' => env('MEMBER_GOOGLE_LOGIN', false),
            'client_id' => env('GOOGLE_CLIENT_ID'),
            'client_secret' => env('GOOGLE_CLIENT_SECRET'),
            'redirect' => env('GOOGLE_REDIRECT_URI', '/v1/member/auth/google/callback'),
        ],
        'facebook' => [
            'enabled' => env('MEMBER_FACEBOOK_LOGIN', false),
            'client_id' => env('FACEBOOK_CLIENT_ID'),
            'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
            'redirect' => env('FACEBOOK_REDIRECT_URI', '/v1/member/auth/facebook/callback'),
        ],
    ],
];
