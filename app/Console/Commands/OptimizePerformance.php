<?php

namespace App\Console\Commands;

use App\Services\PerformanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class OptimizePerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:optimize-performance 
                            {--indexes : Apply recommended database indexes}
                            {--tables : Optimize database tables}
                            {--cache : Warm up application cache}
                            {--all : Run all optimizations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize application performance (indexes, cache, tables)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 Starting Performance Optimization...');
        $this->newLine();

        $runAll = $this->option('all');

        // Laravel built-in optimizations
        $this->info('📦 Running Laravel optimizations...');
        Artisan::call('config:cache');
        $this->line('   ✓ Configuration cached');
        
        Artisan::call('route:cache');
        $this->line('   ✓ Routes cached');
        
        Artisan::call('view:cache');
        $this->line('   ✓ Views cached');
        
        $this->newLine();

        // Database indexes
        if ($this->option('indexes') || $runAll) {
            $this->info('🔍 Applying recommended database indexes...');
            $results = PerformanceService::applyRecommendedIndexes();
            
            foreach ($results as $index => $status) {
                if (str_contains($status, 'created')) {
                    $this->line("   ✓ {$index}: {$status}");
                } else {
                    $this->warn("   ⚠ {$index}: {$status}");
                }
            }
            $this->newLine();
        }

        // Optimize tables
        if ($this->option('tables') || $runAll) {
            $this->info('🗄️  Optimizing database tables...');
            $results = PerformanceService::optimizeTables();
            
            foreach ($results as $table => $status) {
                if ($status === 'optimized') {
                    $this->line("   ✓ {$table}: {$status}");
                } else {
                    $this->error("   ✗ {$table}: {$status}");
                }
            }
            $this->newLine();
        }

        // Warm up cache
        if ($this->option('cache') || $runAll) {
            $this->info('🔥 Warming up application cache...');
            try {
                PerformanceService::warmUpCache();
                $this->line('   ✓ Cache warmed successfully');
            } catch (\Exception $e) {
                $this->error('   ✗ Cache warming failed: ' . $e->getMessage());
            }
            $this->newLine();
        }

        // Show performance metrics
        $this->info('📊 Current Performance Metrics:');
        $metrics = PerformanceService::getPerformanceMetrics();
        
        $this->line('   Memory Usage: ' . $metrics['memory']['current'] . ' (Peak: ' . $metrics['memory']['peak'] . ')');
        $this->line('   Cache Driver: ' . $metrics['cache']['driver']);
        
        $this->newLine();
        $this->info('✅ Performance optimization complete!');
        
        return Command::SUCCESS;
    }
}
