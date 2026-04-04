<?php

namespace App\Services\Admin;

use App\Models\Backup;
use Exception;
use Illuminate\Support\Collection;

class BackupService
{
    public function __construct(
        private ActivityLogService $activityLogService
    ) {}

    /**
     * Create a new database backup.
     *
     * @throws Exception if backup fails
     */
    public function createBackup(string $description = ''): Backup
    {
        // Step 1: Generate unique filename with timestamp
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sqlite";
        $backupDir = storage_path('app/backups');
        $backupPath = "{$backupDir}/{$filename}";

        // Step 2: Ensure backup directory exists
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Step 3: Create backup record with status 'pending'
        $backup = Backup::create([
            'filename'   => $filename,
            'path'       => $backupPath,
            'size'       => 0,
            'description' => $description,
            'created_by' => auth()->id(),
            'status'     => 'pending',
        ]);

        try {
            // Step 4: Copy database file to backup location
            $databasePath = database_path('database.sqlite');

            if (!file_exists($databasePath)) {
                throw new Exception('Database file not found');
            }

            copy($databasePath, $backupPath);

            // Step 5: Update backup record with size and status 'completed'
            $fileSize = filesize($backupPath);
            $backup->update([
                'size'   => $fileSize,
                'status' => 'completed',
            ]);

            // Step 6: Log the action
            $this->activityLogService->log(
                'backup_created',
                "Database backup created: {$filename}",
                auth()->id(),
                ['backup_id' => $backup->id, 'size' => $fileSize]
            );

            return $backup;

        } catch (Exception $e) {
            // Step 7: Mark as failed and log the error
            $backup->update(['status' => 'failed']);

            $this->activityLogService->log(
                'backup_failed',
                "Database backup failed: {$e->getMessage()}",
                auth()->id(),
                ['backup_id' => $backup->id, 'error' => $e->getMessage()]
            );

            // Step 8: Re-throw the exception
            throw $e;
        }
    }

    /**
     * Return all Backup records ordered by created_at descending.
     */
    public function listBackups(): Collection
    {
        return Backup::orderBy('created_at', 'desc')->get();
    }

    /**
     * Find a backup by ID or return null.
     */
    public function getBackup(int $backupId): ?Backup
    {
        return Backup::find($backupId);
    }

    /**
     * Return the file path for download.
     *
     * @throws Exception if the file does not exist on disk
     */
    public function downloadBackup(int $backupId): string
    {
        $backup = Backup::findOrFail($backupId);

        if (!$backup->exists()) {
            throw new Exception('Backup file not found');
        }

        $this->activityLogService->log(
            'backup_downloaded',
            "Backup downloaded: {$backup->filename}",
            auth()->id(),
            ['backup_id' => $backupId]
        );

        return $backup->path;
    }

    /**
     * Delete backup file and record.
     */
    public function deleteBackup(int $backupId): bool
    {
        $backup = Backup::findOrFail($backupId);
        $filename = $backup->filename;

        $backup->delete();

        $this->activityLogService->log(
            'backup_deleted',
            "Backup deleted: {$filename}",
            auth()->id(),
            ['backup_id' => $backupId]
        );

        return true;
    }

    /**
     * Return the size in bytes from the backup record.
     */
    public function getBackupSize(int $backupId): int
    {
        $backup = Backup::findOrFail($backupId);

        return $backup->size;
    }

    /**
     * Delete oldest backups beyond keepCount, return number deleted.
     */
    public function cleanupOldBackups(int $keepCount = 10): int
    {
        $backupsToDelete = Backup::where('status', 'completed')
            ->orderBy('created_at', 'desc')
            ->skip($keepCount)
            ->take(PHP_INT_MAX)
            ->get();

        $deleted = 0;

        foreach ($backupsToDelete as $backup) {
            $filename = $backup->filename;
            $backupId = $backup->id;

            $backup->delete();

            $this->activityLogService->log(
                'backup_deleted',
                "Old backup deleted during cleanup: {$filename}",
                auth()->id(),
                ['backup_id' => $backupId]
            );

            $deleted++;
        }

        return $deleted;
    }
}
