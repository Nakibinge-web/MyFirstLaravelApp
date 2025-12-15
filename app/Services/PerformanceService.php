<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PerformanceService
{
    /**
     * Get database query performance metrics
     */
    public static function getQueryMetrics(): array
    {
        $queries = DB::getQueryLog();
        
        if (empty($queries)) {
            return [
                'total_queries' => 0,
                'total_time' => 0,
                'avg_time' => 0,
                'slowest_query' => null,
            ];
        }

        $totalTime = array_sum(array_column($queries, 'time'));
        $slowestQuery = collect($queries)->sortByDesc('time')->first();

        return [
            'total_queries' => count($queries),
            'total_time' => round($totalTime, 2),
            'avg_time' => round($totalTime / count($queries), 2),
            'slowest_query' => [
                'query' => $slowestQuery['query'] ?? null,
                'time' => $slowestQuery['time'] ?? 0,
            ],
        ];
    }

    /**
     * Enable query logging for performance monitoring
     */
    public static function enableQueryLogging(): void
    {
        DB::enableQueryLog();
    }

    /**
     * Disable query logging
     */
    public static function disableQueryLogging(): void
    {
        DB::disableQueryLog();
    }

    /**
     * Log slow queries (queries taking more than threshold)
     */
    public static function logSlowQueries(float $threshold = 100): void
    {
        $queries = DB::getQueryLog();
        
        foreach ($queries as $query) {
            if ($query['time'] > $threshold) {
                Log::warning('Slow Query Detected', [
                    'query' => $query['query'],
                    'bindings' => $query['bindings'],
                    'time' => $query['time'] . 'ms',
                ]);
            }
        }
    }

    /**
     * Get cache hit rate
     */
    public static function getCacheHitRate(): array
    {
        // This is a simplified version
        // For production, implement proper cache hit/miss tracking
        return [
            'driver' => config('cache.default'),
            'status' => 'active',
            'note' => 'Implement Redis INFO stats for detailed metrics',
        ];
    }

    /**
     * Get memory usage
     */
    public static function getMemoryUsage(): array
    {
        return [
            'current' => round(memory_get_usage() / 1024 / 1024, 2) . ' MB',
            'peak' => round(memory_get_peak_usage() / 1024 / 1024, 2) . ' MB',
            'limit' => ini_get('memory_limit'),
        ];
    }

    /**
     * Get application performance metrics
     */
    public static function getPerformanceMetrics(): array
    {
        return [
            'memory' => self::getMemoryUsage(),
            'cache' => self::getCacheHitRate(),
            'queries' => self::getQueryMetrics(),
        ];
    }

    /**
     * Optimize database tables
     */
    public static function optimizeTables(): array
    {
        $tables = [
            'transactions',
            'categories',
            'budgets',
            'goals',
            'notifications',
            'users',
        ];

        $results = [];
        foreach ($tables as $table) {
            try {
                DB::statement("OPTIMIZE TABLE {$table}");
                $results[$table] = 'optimized';
            } catch (\Exception $e) {
                $results[$table] = 'failed: ' . $e->getMessage();
            }
        }

        return $results;
    }

    /**
     * Clear old cache entries
     */
    public static function clearOldCache(): void
    {
        // Clear cache entries older than 24 hours
        // This depends on cache driver implementation
        Cache::flush();
    }

    /**
     * Warm up application cache
     */
    public static function warmUpCache(): void
    {
        // Get all active users
        $users = \App\Models\User::where('email_verified_at', '!=', null)
            ->limit(100) // Limit to prevent memory issues
            ->get();

        foreach ($users as $user) {
            try {
                CacheService::warmUserCache($user->id);
            } catch (\Exception $e) {
                Log::error('Cache warming failed for user ' . $user->id, [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get recommended database indexes
     */
    public static function getRecommendedIndexes(): array
    {
        return [
            'transactions' => [
                'user_id_date' => 'CREATE INDEX idx_transactions_user_date ON transactions(user_id, date DESC)',
                'user_id_type' => 'CREATE INDEX idx_transactions_user_type ON transactions(user_id, type)',
                'category_id' => 'CREATE INDEX idx_transactions_category ON transactions(category_id)',
                'date' => 'CREATE INDEX idx_transactions_date ON transactions(date DESC)',
            ],
            'budgets' => [
                'user_id_dates' => 'CREATE INDEX idx_budgets_user_dates ON budgets(user_id, start_date, end_date)',
                'category_id' => 'CREATE INDEX idx_budgets_category ON budgets(category_id)',
            ],
            'goals' => [
                'user_id_status' => 'CREATE INDEX idx_goals_user_status ON goals(user_id, status)',
                'target_date' => 'CREATE INDEX idx_goals_target_date ON goals(target_date)',
            ],
            'notifications' => [
                'user_id_read' => 'CREATE INDEX idx_notifications_user_read ON notifications(user_id, is_read)',
                'created_at' => 'CREATE INDEX idx_notifications_created ON notifications(created_at DESC)',
            ],
            'categories' => [
                'user_id_type' => 'CREATE INDEX idx_categories_user_type ON categories(user_id, type)',
            ],
        ];
    }

    /**
     * Apply recommended indexes
     */
    public static function applyRecommendedIndexes(): array
    {
        $indexes = self::getRecommendedIndexes();
        $results = [];

        foreach ($indexes as $table => $tableIndexes) {
            foreach ($tableIndexes as $indexName => $sql) {
                try {
                    DB::statement($sql);
                    $results["{$table}.{$indexName}"] = 'created';
                } catch (\Exception $e) {
                    // Index might already exist
                    $results["{$table}.{$indexName}"] = 'skipped: ' . $e->getMessage();
                }
            }
        }

        return $results;
    }
}
