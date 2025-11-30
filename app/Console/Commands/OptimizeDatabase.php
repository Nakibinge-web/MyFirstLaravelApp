<?php

namespace App\Console\Commands;

use App\Services\QueryOptimizationService;
use Illuminate\Console\Command;

class OptimizeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fintrack:optimize-db {--stats : Show table statistics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize database tables and indexes for better performance';

    protected $optimizationService;

    public function __construct(QueryOptimizationService $optimizationService)
    {
        parent::__construct();
        $this->optimizationService = $optimizationService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database optimization...');

        if ($this->option('stats')) {
            $this->showStatistics();
            return Command::SUCCESS;
        }

        // Optimize transactions table
        $this->info('Optimizing transactions table...');
        $this->optimizationService->optimizeTransactionsTable();

        // Optimize budgets table
        $this->info('Optimizing budgets table...');
        $this->optimizationService->optimizeBudgetsTable();

        // Optimize goals table
        $this->info('Optimizing goals table...');
        $this->optimizationService->optimizeGoalsTable();

        // Optimize all tables
        $this->info('Running OPTIMIZE TABLE on all tables...');
        $this->optimizationService->optimizeAllTables();

        $this->info('✓ Database optimization completed successfully!');

        // Show statistics
        $this->newLine();
        $this->showStatistics();

        return Command::SUCCESS;
    }

    /**
     * Show table statistics
     */
    protected function showStatistics()
    {
        $this->info('Database Statistics:');
        $this->newLine();

        $stats = $this->optimizationService->getTableStatistics();

        $headers = ['Table', 'Rows', 'Size (MB)'];
        $rows = [];

        foreach ($stats as $table => $data) {
            $rows[] = [
                $table,
                number_format($data['rows']),
                $data['size'],
            ];
        }

        $this->table($headers, $rows);
    }
}
