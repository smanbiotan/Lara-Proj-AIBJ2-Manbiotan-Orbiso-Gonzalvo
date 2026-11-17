<?php

return [
    // First administrator account (used by `php artisan db:seed`). Change before deployment.
    'admin_name' => env('SALON_ADMIN_NAME', 'Blush & Bloom Salon Admin'),
    'admin_email' => strtolower((string) env('SALON_ADMIN_EMAIL', 'admin@blushbloom.salon')),
    'admin_password' => env('SALON_ADMIN_PASSWORD'),

    // "Continue with Google" for customers (Google Cloud Console → OAuth client, type Web application).
    // Authorized redirect URI: APP_URL + /auth/google/callback
    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    // Use real Unsplash photos for the default images (set false to use local files in public/images).
    'remote_images' => (bool) env('SALON_REMOTE_IMAGES', true),

    'timezone' => env('APP_TIMEZONE', 'Asia/Manila'),

    'n8n' => [
        // Laravel -> n8n (Workflow 1: AI assistant)
        'chat_webhook_url' => env('N8N_CHAT_WEBHOOK_URL'),
        // Laravel -> n8n (Workflow 2: appointment emails)
        'appointment_webhook_url' => env('N8N_APPOINTMENT_WEBHOOK_URL'),
        // Shared secret Laravel sends in the X-Webhook-Secret header to n8n.
        'webhook_secret' => env('N8N_WEBHOOK_SECRET'),
        // Bearer token n8n must send when calling Laravel's /api/n8n/* tools.
        'api_token' => env('N8N_API_TOKEN'),
        'timeout' => (int) env('N8N_TIMEOUT', 10),
        'chat_timeout' => (int) env('N8N_CHAT_TIMEOUT', 45),
    ],
];
