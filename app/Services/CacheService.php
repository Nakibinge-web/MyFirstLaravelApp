<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Get cache TTL from config
     */
    private static function getTTL(string $key): int
    {
        return config("cache-settings.ttl.{$key}", 300);
    }

    /**
     * Generate cache key with prefix
     */
    private static function cacheKey(string $key, ...$params): string
    {
        $prefix = config('cache.prefix', 'fintrack');
        return $prefix . '_' . $key . '_' . implode('_', $params);
    }

    // ==================== Dashboard Caching ====================

    /**
     * Clear user's dashboard cache
     */
    public static function clearDashboardCache($userId): void
    {
        Cache::forget(self::cacheKey('dashboard_data', 'user', $userId));
    }

    // ==================== Categories Caching ====================

    /**
     * Get user's categories from cache
     */
    public static function getUserCategories($userId)
    {
        $cacheKey = self::cacheKey('user_categories', $userId);
        $ttl = self::getTTL('categories');
        
        return Cache::remember($cacheKey, $ttl, function () use ($userId) {
            return \App\Models\Category::where('user_id', $userId)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Clear user's categories cache
     */
    public static function clearCategoriesCache($userId): void
    {
        Cache::forget(self::cacheKey('user_categories', $userId));
    }

    // ==================== Budgets Caching ====================

    /**
     * Get user's active budgets from cache
     */
    public static function getUserActiveBudgets($userId)
    {
        $cacheKey = self::cacheKey('user_active_budgets', $userId);
        $ttl = self::getTTL('budgets');
        
        return Cache::remember($cacheKey, $ttl, function () use ($userId) {
            return \App\Models\Budget::where('user_id', $userId)
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->with('category')
                ->get();
        });
    }

    /**
     * Clear user's budgets cache
     */
    public static function clearBudgetsCache($userId): void
    {
        Cache::forget(self::cacheKey('user_active_budgets', $userId));
    }

    // ==================== Goals Caching ====================

    /**
     * Get user's active goals from cache
     */
    public static function getUserActiveGoals($userId)
    {
        $cacheKey = self::cacheKey('user_active_goals', $userId);
        $ttl = self::getTTL('goals');
        
        return Cache::remember($cacheKey, $ttl, function () use ($userId) {
            return \App\Models\Goal::where('user_id', $userId)
                ->where('status', 'active')
                ->orderBy('target_date')
                ->get();
        });
    }

    /**
     * Clear user's goals cache
     */
    public static function clearGoalsCache($userId): void
    {
        Cache::forget(self::cacheKey('user_active_goals', $userId));
    }

    // ==================== Transactions Caching ====================

    /**
     * Get user's monthly transaction summary from cache
     */
    public static function getMonthlyTransactionSummary($userId, $year, $month)
    {
        $cacheKey = self::cacheKey('monthly_transaction_summary', $userId, $year, $month);
        $ttl = self::getTTL('transaction_summary');
        
        return Cache::remember($cacheKey, $ttl, function () use ($userId, $year, $month) {
            $startDate = \Carbon\Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = $startDate->copy()->endOfMonth();
            
            $transactions = \App\Models\Transaction::where('user_id', $userId)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
            
            return [
                'total_income' => $transactions->where('type', 'income')->sum('amount'),
                'total_expenses' => $transactions->where('type', 'expense')->sum('amount'),
                'transaction_count' => $transactions->count(),
                'net_balance' => $transactions->where('type', 'income')->sum('amount') - 
                                $transactions->where('type', 'expense')->sum('amount'),
            ];
        });
    }

    /**
     * Clear transaction-related caches
     */
    public static function clearTransactionCache($userId): void
    {
        // Clear current month summary
        $year = now()->year;
        $month = now()->month;
        Cache::forget(self::cacheKey('monthly_transaction_summary', $userId, $year, $month));
        
        // Clear dashboard cache (contains recent transactions)
        self::clearDashboardCache($userId);
        
        // Clear monthly stats
        self::clearMonthlyStatsCache($userId);
    }

    // ==================== Monthly Stats Caching ====================

    /**
     * Get user's monthly statistics from cache
     */
    public static function getUserMonthlyStats($userId)
    {
        $cacheKey = self::cacheKey('user_monthly_stats', $userId, now()->format('Y_m'));
        $ttl = self::getTTL('monthly_stats');
        
        return Cache::remember($cacheKey, $ttl, function () use ($userId) {
            // This will be populated by the service that calculates stats
            return null;
        });
    }

    /**
     * Clear user's monthly stats cache
     */
    public static function clearMonthlyStatsCache($userId): void
    {
        $cacheKey = self::cacheKey('user_monthly_stats', $userId, now()->format('Y_m'));
        Cache::forget($cacheKey);
    }

    // ==================== Reports Caching ====================

    /**
     * Get monthly report from cache
     */
    public static function getMonthlyReport($userId, $year, $month)
    {
        $cacheKey = self::cacheKey('monthly_report', $userId, $year, $month);
        $ttl = self::getTTL('reports');
        
        return Cache::remember($cacheKey, $ttl, function () use ($userId, $year, $month) {
            // This will be populated by ReportService
            return null;
        });
    }

    /**
     * Clear reports cache
     */
    public static function clearReportsCache($userId, $year = null, $month = null): void
    {
        if ($year && $month) {
            Cache::forget(self::cacheKey('monthly_report', $userId, $year, $month));
        } else {
            // Clear current month report
            Cache::forget(self::cacheKey('monthly_report', $userId, now()->year, now()->month));
        }
    }

    // ==================== Notifications Caching ====================

    /**
     * Get user's unread notifications count from cache
     */
    public static function getUnreadNotificationsCount($userId)
    {
        $cacheKey = self::cacheKey('unread_notifications_count', $userId);
        $ttl = self::getTTL('notifications');
        
        return Cache::remember($cacheKey, $ttl, function () use ($userId) {
            return \App\Models\Notification::where('user_id', $userId)
                ->where('is_read', false)
                ->count();
        });
    }

    /**
     * Clear notifications cache
     */
    public static function clearNotificationsCache($userId): void
    {
        Cache::forget(self::cacheKey('unread_notifications_count', $userId));
    }

    // ==================== Currency Caching ====================

    /**
     * Get currency exchange rates (cached for 24 hours)
     */
    public static function getCurrencyRates()
    {
        $cacheKey = self::cacheKey('currency_rates');
        $ttl = self::getTTL('currency');
        
        return Cache::remember($cacheKey, $ttl, function () {
            // In production, fetch from API
            // For now, return static rates
            return [
                'USD' => 1.0,
                'EUR' => 0.85,
                'GBP' => 0.73,
                'JPY' => 110.0,
                'CNY' => 6.45,
                'INR' => 74.50,
                'CAD' => 1.25,
                'AUD' => 1.35,
                'CHF' => 0.92,
                'SEK' => 8.60,
            ];
        });
    }

    // ==================== User Profile Caching ====================

    /**
     * Get user profile from cache
     */
    public static function getUserProfile($userId)
    {
        $cacheKey = self::cacheKey('user_profile', $userId);
        $ttl = self::getTTL('user_profile');
        
        return Cache::remember($cacheKey, $ttl, function () use ($userId) {
            return \App\Models\User::find($userId);
        });
    }

    /**
     * Clear user profile cache
     */
    public static function clearUserProfileCache($userId): void
    {
        Cache::forget(self::cacheKey('user_profile', $userId));
    }

    // ==================== Bulk Cache Operations ====================

    /**
     * Clear all user-related caches
     */
    public static function clearAllUserCache($userId): void
    {
        self::clearDashboardCache($userId);
        self::clearCategoriesCache($userId);
        self::clearBudgetsCache($userId);
        self::clearGoalsCache($userId);
        self::clearTransactionCache($userId);
        self::clearMonthlyStatsCache($userId);
        self::clearReportsCache($userId);
        self::clearNotificationsCache($userId);
        self::clearUserProfileCache($userId);
    }

    /**
     * Warm up cache for a user (preload frequently accessed data)
     */
    public static function warmUserCache($userId): void
    {
        self::getUserCategories($userId);
        self::getUserActiveBudgets($userId);
        self::getUserActiveGoals($userId);
        self::getUnreadNotificationsCount($userId);
    }

    /**
     * Clear all application cache
     */
    public static function clearAllCache(): void
    {
        Cache::flush();
    }

    /**
     * Get cache statistics
     */
    public static function getCacheStats(): array
    {
        // This is a basic implementation
        // For production, consider using Redis INFO command or similar
        return [
            'driver' => config('cache.default'),
            'prefix' => config('cache.prefix'),
            'enabled' => true,
        ];
    }
}
