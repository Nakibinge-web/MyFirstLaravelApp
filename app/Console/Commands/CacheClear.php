<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;

class CacheClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fintrack:cache-clear {--all : Clear all caches} {--user= : Clear cache for specific user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear Financial Tracker application caches';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            $this->clearAllCaches();
        } elseif ($userId = $this->option('user')) {
            $this->clearUserCache($userId);
        } else {
            $this->clearApplicationCache();
        }

        return Command::SUCCESS;
    }

    /**
     * Clear all application caches
     */
    protected function clearAllCaches()
    {
        $this->info('Clearing all caches...');

        // Clear application cache
        Artisan::call('cache:clear');
        $this->info('✓ Application cache cleared');

        // Clear config cache
        Artisan::call('config:clear');
        $this->info('✓ Configuration cache cleared');

        // Clear route cache
        Artisan::call('route:clear');
        $this->info('✓ Route cache cleared');

        // Clear view cache
        Artisan::call('view:clear');
        $this->info('✓ View cache cleared');

        $this->info('All caches cleared successfully!');
    }

    /**
     * Clear cache for specific user
     */
    protected function clearUserCache($userId)
    {
        $this->info("Clearing cache for user {$userId}...");

        $prefix = config('cache-settings.prefix', 'fintrack_');

        // Clear user-specific caches
        Cache::forget("{$prefix}dashboard_data_user_{$userId}");
        Cache::forget("{$prefix}user_categories_{$userId}");
        Cache::forget("{$prefix}user_active_budgets_{$userId}");
        Cache::forget("{$prefix}user_active_goals_{$userId}");

        // Clear monthly stats for current month
        $monthKey = now()->format('Y_m');
        Cache::forget("{$prefix}user_monthly_stats_{$userId}_{$monthKey}");

        $this->info("✓ Cache cleared for user {$userId}");
    }

    /**
     * Clear only application cache
     */
    protected function clearApplicationCache()
    {
        $this->info('Clearing application cache...');
        
        Artisan::call('cache:clear');
        
        $this->info('✓ Application cache cleared successfully!');
    }
}
