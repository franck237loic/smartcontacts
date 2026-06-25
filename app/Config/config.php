<?php

/**
 * Configuration File
 * GlobalPhone Analytics - MVC Architecture
 */

return [
    // Database Configuration
    'database' => [
        'host' => getenv('DB_HOST') ?: 'localhost',
        'dbname' => getenv('DB_NAME') ?: 'smartcontacts',
        'username' => getenv('DB_USER') ?: 'root',
        'password' => getenv('DB_PASS') ?: '',
        'charset' => 'utf8mb4',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    ],

    // Application Configuration
    'app' => [
        'name' => 'GlobalPhone Analytics',
        'env' => getenv('APP_ENV') ?: 'development', // development, production
        'debug' => getenv('APP_ENV') === 'development',
        'timezone' => 'Europe/Paris',
        'locale' => 'fr_FR',
        'url' => getenv('APP_URL') ?: 'https://smartcontacts.free.nf/smartcontacts'
    ],

    // API Configuration
    'api' => [
        'version' => 'v1',
        'rate_limit' => [
            'enabled' => true,
            'requests_per_minute' => 60,
            'requests_per_hour' => 1000
        ],
        'authentication' => [
            'enabled' => true,
            'token_expiry' => 3600 // 1 hour
        ]
    ],

    // Security Configuration
    'security' => [
        'csrf_token' => true,
        'password_hash' => PASSWORD_BCRYPT,
        'session_lifetime' => 7200, // 2 hours
        'allowed_origins' => ['*'] // Configure for production
    ],

    // View Configuration
    'view' => [
        'path' => __DIR__ . '/../Views',
        'cache_path' => __DIR__ . '/../Cache',
        'extension' => '.php'
    ],

    // Logging Configuration
    'logging' => [
        'enabled' => true,
        'path' => __DIR__ . '/../../logs',
        'level' => 'debug' // debug, info, warning, error
    ],

    // Stripe Configuration
    'stripe' => [
        'secret_key' => getenv('STRIPE_SECRET_KEY') ?: 'sk_test_your_secret_key_here',
        'publishable_key' => getenv('STRIPE_PUBLISHABLE_KEY') ?: 'pk_test_your_publishable_key_here',
        'webhook_secret' => getenv('STRIPE_WEBHOOK_SECRET') ?: '',
        'currency' => 'EUR',
        'success_url' => '?subscription/success?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => '?subscription/plans'
    ],

    // Redis Configuration
    'redis' => [
        'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
        'port' => getenv('REDIS_PORT') ?: 6379,
        'password' => getenv('REDIS_PASSWORD') ?: null,
        'database' => 0
    ]
];
