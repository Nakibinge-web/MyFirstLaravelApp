<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ClearCacheOnUpdate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Clear cache after successful POST, PUT, PATCH, DELETE requests
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE']) && 
            $response->isSuccessful()) {
            
            $userId = auth()->id();
            
            if ($userId) {
                // Clear user-specific caches
                $this->clearUserCaches($userId);
            }
        }

        return $response;
    }

    /**
     * Clear all user-related caches
     */
    protected function clearUserCaches($userId)
    {
        $prefix = config('cache-settings.prefix', 'fintrack_');
        
        // Clear dashboard cache
        Cache::forget("{$prefix}dashboard_data_user_{$userId}");
        
        // Clear categories cache
        Cache::forget("{$prefix}user_categories_{$userId}");
        
        // Clear budgets cache
        Cache::forget("{$prefix}user_active_budgets_{$userId}");
        
        // Clear monthly stats cache
        $monthKey = now()->format('Y_m');
        Cache::forget("{$prefix}user_monthly_stats_{$userId}_{$monthKey}");
        
        // Clear goals cache
        Cache::forget("{$prefix}user_active_goals_{$userId}");
    }
}
