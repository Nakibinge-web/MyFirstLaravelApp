<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache TTL (Time To Live) Settings
    |--------------------------------------------------------------------------
    |
    | Define cache durations in seconds for different parts of the application.
    | Adjust these values based on your application's needs and data freshness requirements.
    |
    */

    'ttl' => [
        // Dashboard caching (5 minutes)
        'dashboard' => env('CACHE_TTL_DASHBOARD', 300),
        
        // Categories caching (1 hour - rarely changes)
        'categories' => env('CACHE_TTL_CATEGORIES', 3600),
        
        // Budgets caching (5 minutes)
        'budgets' => env('CACHE_TTL_BUDGETS', 300),
        
        // Goals caching (5 minutes)
        'goals' => env('CACHE_TTL_GOALS', 300),
        
        // Monthly statistics (10 minutes)
        'monthly_stats' => env('CACHE_TTL_MONTHLY_STATS', 600),
        
        // Reports caching (15 minutes)
        'reports' => env('CACHE_TTL_REPORTS', 900),
        
        // Currency rates (24 hours)
        'currency' => env('CACHE_TTL_CURRENCY', 86400),
        
        // User profile (30 minutes)
        'user_profile' => env('CACHE_TTL_USER_PROFILE', 1800),
        
        // Notifications (2 minutes)
        'notifications' => env('CACHE_TTL_NOTIFICATIONS', 120),
        
        // Transaction summaries (5 minutes)
        'transaction_summary' => env('CACHE_TTL_TRANSACTION_SUMMARY', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Cache Settings
    |--------------------------------------------------------------------------
    |
    | Enable/disable query result caching for expensive database queries.
    |
    */

    'query_cache' => [
        'enabled' => env('QUERY_CACHE_ENABLED', true),
        'ttl' => env('QUERY_CACHE_TTL', 300), // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Tags
    |--------------------------------------------------------------------------
    |
    | Define cache tags for easier cache invalidation.
    | Note: Tags are only supported by certain cache drivers (Redis, Memcached).
    |
    */

    'tags' => [
        'users' => 'users',
        'transactions' => 'transactions',
        'categories' => 'categories',
        'budgets' => 'budgets',
        'goals' => 'goals',
        'reports' => 'reports',
        'notifications' => 'notifications',
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Warming
    |--------------------------------------------------------------------------
    |
    | Enable automatic cache warming for frequently accessed data.
    |
    */

    'warming' => [
        'enabled' => env('CACHE_WARMING_ENABLED', false),
        'schedule' => '*/30 * * * *', // Every 30 minutes
    ],
];
