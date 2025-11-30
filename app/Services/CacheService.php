<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class CacheService
{
    /**
     * Cache duration in minutes
     */
    const CACHE_DURATION = 60; // 1 hour
    const SHORT_CACHE = 5; // 5 minutes
    const LONG_CACHE = 1440; // 24 hours

    /**
     * Get user's dashboard data from cache or generate
     */
    public function getUserDashboardData($userId)
    {
        $cacheKey = "dashboard_data_{$userId}";
        
        return Cache::remember($cacheKey, self::SHORT_CACHE, function () use ($userId) {
            // This will be populated by DashboardController
            return null;
        });
    }

    /**
     * Clear user's dashboard cache
     */
    public function clearUserDashboardCache($userId)
    {
        Cache::forget("dashboard_data_{$userId}");
    }

    /**
     * Get user's categories from cache
     */
    public function getUserCategories($userId)
    {
        $cacheKey = "user_categories_{$userId}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId) {
            return \App\Models\Category::where('user_id', $userId)->get();
        });
    }

    /**
     * Clear user's categories cache
     */
    public function clearUserCategoriesCache($userId)
    {
        Cache::forget("user_categories_{$userId}");
    }

    /**
     * Get user's active budgets from cache
     */
    public function getUserActiveBudgets($userId)
    {
        $cacheKey = "user_active_budgets_{$userId}";
        
        return Cache::remember($cacheKey, self::SHORT_CACHE, function () use ($userId) {
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
    public function clearUserBudgetsCache($userId)
    {
        Cache::forget("user_active_budgets_{$userId}");
    }

    /**
     * Get user's monthly statistics from cache
     */
    public function getUserMonthlyStats($userId)
    {
        $cacheKey = "user_monthly_stats_{$userId}_" . now()->format('Y_m');
        
        return Cache::remember($cacheKey, self::SHORT_CACHE, function () use ($userId) {
            // This will be populated by the service that calculates stats
            return null;
        });
    }

    /**
     * Clear user's monthly stats cache
     */
    public function clearUserMonthlyStatsCache($userId)
    {
        $cacheKey = "user_monthly_stats_{$userId}_" . now()->format('Y_m');
        Cache::forget($cacheKey);
    }

    /**
     * Clear all user-related caches
     */
    public function clearAllUserCache($userId)
    {
        $this->clearUserDashboardCache($userId);
        $this->clearUserCategoriesCache($userId);
        $this->clearUserBudgetsCache($userId);
        $this->clearUserMonthlyStatsCache($userId);
    }

    /**
     * Get currency exchange rates (cached for 24 hours)
     */
    public function getCurrencyRates()
    {
        $cacheKey = "currency_rates";
        
        return Cache::remember($cacheKey, self::LONG_CACHE, function () {
            // In production, fetch from API
            // For now, return static rates
            return [
                'USD' => 1.0,
                'EUR' => 0.85,
                'GBP' => 0.73,
                'JPY' => 110.0,
                // Add more currencies as needed
            ];
        });
    }
}
