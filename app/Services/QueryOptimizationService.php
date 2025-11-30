<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QueryOptimizationService
{
    /**
     * Enable query logging for debugging
     */
    public function enableQueryLog()
    {
        DB::enableQueryLog();
    }

    /**
     * Get executed queries
     */
    public function getQueryLog()
    {
        return DB::getQueryLog();
    }

    /**
     * Log slow queries (queries taking more than threshold)
     */
    public function logSlowQueries($threshold = 1000) // 1 second
    {
        DB::listen(function ($query) use ($threshold) {
            if ($query->time > $threshold) {
                Log::warning('Slow Query Detected', [
                    'sql' => $query->sql,
                    'bindings' => $query->bindings,
                    'time' => $query->time . 'ms',
                ]);
            }
        });
    }

    /**
     * Optimize transactions table
     */
    public function optimizeTransactionsTable()
    {
        // Add indexes if not exists
        $indexes = [
            'transactions_user_id_date_index' => 'ALTER TABLE transactions ADD INDEX IF NOT EXISTS transactions_user_id_date_index (user_id, date)',
            'transactions_category_id_index' => 'ALTER TABLE transactions ADD INDEX IF NOT EXISTS transactions_category_id_index (category_id)',
            'transactions_type_index' => 'ALTER TABLE transactions ADD INDEX IF NOT EXISTS transactions_type_index (type)',
        ];

        foreach ($indexes as $name => $sql) {
            try {
                DB::statement($sql);
                Log::info("Index created: {$name}");
            } catch (\Exception $e) {
                Log::info("Index already exists or error: {$name}");
            }
        }
    }

    /**
     * Optimize budgets table
     */
    public function optimizeBudgetsTable()
    {
        $indexes = [
            'budgets_user_id_dates_index' => 'ALTER TABLE budgets ADD INDEX IF NOT EXISTS budgets_user_id_dates_index (user_id, start_date, end_date)',
            'budgets_category_id_index' => 'ALTER TABLE budgets ADD INDEX IF NOT EXISTS budgets_category_id_index (category_id)',
        ];

        foreach ($indexes as $name => $sql) {
            try {
                DB::statement($sql);
                Log::info("Index created: {$name}");
            } catch (\Exception $e) {
                Log::info("Index already exists or error: {$name}");
            }
        }
    }

    /**
     * Optimize goals table
     */
    public function optimizeGoalsTable()
    {
        $indexes = [
            'goals_user_id_status_index' => 'ALTER TABLE goals ADD INDEX IF NOT EXISTS goals_user_id_status_index (user_id, status)',
            'goals_target_date_index' => 'ALTER TABLE goals ADD INDEX IF NOT EXISTS goals_target_date_index (target_date)',
        ];

        foreach ($indexes as $name => $sql) {
            try {
                DB::statement($sql);
                Log::info("Index created: {$name}");
            } catch (\Exception $e) {
                Log::info("Index already exists or error: {$name}");
            }
        }
    }

    /**
     * Analyze and optimize all tables
     */
    public function optimizeAllTables()
    {
        $tables = ['users', 'categories', 'transactions', 'budgets', 'goals', 'notifications'];

        foreach ($tables as $table) {
            try {
                DB::statement("OPTIMIZE TABLE {$table}");
                Log::info("Table optimized: {$table}");
            } catch (\Exception $e) {
                Log::error("Failed to optimize table {$table}: " . $e->getMessage());
            }
        }
    }

    /**
     * Get table statistics
     */
    public function getTableStatistics()
    {
        $tables = ['users', 'categories', 'transactions', 'budgets', 'goals', 'notifications'];
        $stats = [];

        foreach ($tables as $table) {
            $count = DB::table($table)->count();
            $stats[$table] = [
                'rows' => $count,
                'size' => $this->getTableSize($table),
            ];
        }

        return $stats;
    }

    /**
     * Get table size in MB
     */
    private function getTableSize($table)
    {
        try {
            $result = DB::select("
                SELECT 
                    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS size_mb
                FROM information_schema.TABLES 
                WHERE table_schema = DATABASE()
                AND table_name = ?
            ", [$table]);

            return $result[0]->size_mb ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Clean up old data (optional maintenance task)
     */
    public function cleanupOldData($days = 365)
    {
        $date = now()->subDays($days);

        // Delete old notifications
        $deletedNotifications = DB::table('notifications')
            ->where('created_at', '<', $date)
            ->where('is_read', true)
            ->delete();

        Log::info("Cleaned up {$deletedNotifications} old notifications");

        return [
            'notifications_deleted' => $deletedNotifications,
        ];
    }
}
