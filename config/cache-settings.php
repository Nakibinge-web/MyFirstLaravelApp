<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cache Time-To-Live (TTL) Settings
    |--------------------------------------------------------------------------
    |
    | Define cache durations in seconds for different data types
    |
    */

    'ttl' => [
        // Dashboard data - 5 minutes
        'dashboard' => env('CACHE_TTL_DASHBOARD', 300),
        
        // User categories - 1 hour
        'categories' => env('CACHE_TTL_CATEGORIES', 3600),
        
        // Budget data - 5 minutes
        'budgets' => env('CACHE_TTL_BUDGETS', 300),
        
        // Goals data - 5 minutes
        'goals' => env('CACHE_TTL_GOALS', 300),
        
        // Monthly statistics - 10 minutes
        'monthly_stats' => env('CACHE_TTL_MONTHLY_STATS', 600),
        
        // Reports - 15 minutes
        'reports' => env('CACHE_TTL_REPORTS', 900),
        
        // Currency rates - 24 hours
        'currency_rates' => env('CACHE_TTL_CURRENCY', 86400),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Keys Prefix
    |--------------------------------------------------------------------------
    |
    | Prefix for all cache keys to avoid conflicts
    |
    */

    'prefix' => env('CACHE_PREFIX', 'fintrack_'),

    /*
    |--------------------------------------------------------------------------
    | Cache Driver
    |--------------------------------------------------------------------------
    |
    | Supported: "file", "database", "redis", "memcached"
    |
    */

    'driver' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Query Cache Settings
    |--------------------------------------------------------------------------
    |
    | Enable/disable query result caching
    |
    */

    'query_cache' => [
        'enabled' => env('QUERY_CACHE_ENABLED', true),
        'ttl' => env('QUERY_CACHE_TTL', 300), // 5 minutes
    ],
];
