<?php

namespace App\Console\Commands;

use App\Services\Admin\BackupService;
use Illuminate\Console\Command;

class BackupCleanupJob extends Command
{
    protected $signature = 'admin:cleanup-backups {--keep=10 : Number of recent backups to keep}';

    protected $description = 'Remove old backups, keeping only the most recent ones';

    public function __construct(private BackupService $backupService)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $keep = (int) $this->option('keep');

        $deleted = $this->backupService->cleanupOldBackups($keep);

        $this->info("Deleted {$deleted} old backup(s), keeping the last {$keep}.");

        return Command::SUCCESS;
    }
}
