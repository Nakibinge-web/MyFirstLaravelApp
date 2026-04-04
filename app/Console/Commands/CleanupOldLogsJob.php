<?php

namespace App\Console\Commands;

use App\Services\Admin\ActivityLogService;
use Illuminate\Console\Command;

class CleanupOldLogsJob extends Command
{
    protected $signature = 'admin:cleanup-logs {--days=90 : Number of days to keep logs}';

    protected $description = 'Delete activity logs older than the specified number of days';

    public function __construct(private ActivityLogService $activityLogService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $deleted = $this->activityLogService->deleteOldLogs($days);

        $this->info("Deleted {$deleted} activity log(s) older than {$days} days.");

        return Command::SUCCESS;
    }
}
