<?php

namespace App\Services\Admin;

use App\Models\Backup;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        // Step 1: Generate unique filename with timestamp based on database driver
        $timestamp = now()->format('Y-m-d_H-i-s_u');
        $driver = config('database.default');
        $databasePath = config('database.connections.sqlite.database');
        $isRealSqliteFile = ($driver === 'sqlite' && $databasePath && $databasePath !== ':memory:' && file_exists($databasePath));

        $ext = $isRealSqliteFile ? 'sqlite' : 'sql';
        $filename = "backup_{$timestamp}.{$ext}";
        $backupDir = storage_path('app/backups');
        $backupPath = "{$backupDir}/{$filename}";

        // Step 2: Ensure backup directory exists
        if (!file_exists($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        // Step 3: Create backup record with status 'pending'
        $backup = Backup::create([
            'filename'    => $filename,
            'path'        => $backupPath,
            'size'        => 0,
            'description' => $description,
            'created_by'  => auth()->id(),
            'status'      => 'pending',
        ]);

        try {
            // Step 4: Perform backup according to active database driver
            if ($isRealSqliteFile) {
                copy($databasePath, $backupPath);
            } else {
                $this->exportSqlBackup($backupPath);
            }

            // Step 5: Update backup record with size and status 'completed'
            $fileSize = file_exists($backupPath) ? filesize($backupPath) : 0;
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
     * Export database tables and rows to an SQL dump file.
     *
     * @throws Exception
     */
    protected function exportSqlBackup(string $backupPath): void
    {
        $handle = fopen($backupPath, 'w');
        if (!$handle) {
            throw new Exception("Unable to open backup file for writing: {$backupPath}");
        }

        $driver = config('database.default');
        fwrite($handle, "-- Personal Financial Tracker Database Backup\n");
        fwrite($handle, "-- Generated at: " . now()->toDateTimeString() . "\n");
        fwrite($handle, "-- Connection: {$driver}\n\n");

        if ($driver === 'mysql') {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=0;\n\n");
        } elseif ($driver === 'sqlite') {
            fwrite($handle, "PRAGMA foreign_keys = OFF;\n\n");
        }

        $tables = DB::connection()->getSchemaBuilder()->getTableListing();

        foreach ($tables as $table) {
            if ($table === 'sqlite_sequence') {
                continue;
            }

            try {
                if ($driver === 'sqlite') {
                    $createTableResult = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name = ?", [$table]);
                    if (!empty($createTableResult) && !empty($createTableResult[0]->sql)) {
                        fwrite($handle, "DROP TABLE IF EXISTS \"{$table}\";\n");
                        fwrite($handle, $createTableResult[0]->sql . ";\n\n");
                    }
                } else {
                    $createTableResult = DB::select("SHOW CREATE TABLE `{$table}`");
                    if (!empty($createTableResult)) {
                        $createTableObj = (array) $createTableResult[0];
                        $createTableSql = $createTableObj['Create Table'] ?? array_values($createTableObj)[1] ?? null;
                        if ($createTableSql) {
                            fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");
                            fwrite($handle, $createTableSql . ";\n\n");
                        }
                    }
                }
            } catch (Exception $e) {
                // If SHOW CREATE TABLE is unsupported on the connection, proceed with rows
            }

            $rows = DB::table($table)->get();
            if ($rows->isNotEmpty()) {
                foreach ($rows as $row) {
                    $rowArray = (array) $row;
                    $quoteChar = ($driver === 'sqlite') ? '"' : '`';
                    $columns = array_map(fn($col) => "{$quoteChar}{$col}{$quoteChar}", array_keys($rowArray));
                    $values = array_map(function ($val) {
                        if (is_null($val)) {
                            return 'NULL';
                        }
                        return DB::getPdo()->quote((string) $val);
                    }, array_values($rowArray));

                    fwrite($handle, "INSERT INTO {$quoteChar}{$table}{$quoteChar} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n");
                }
                fwrite($handle, "\n");
            }
        }

        if ($driver === 'mysql') {
            fwrite($handle, "SET FOREIGN_KEY_CHECKS=1;\n");
        } elseif ($driver === 'sqlite') {
            fwrite($handle, "PRAGMA foreign_keys = ON;\n");
        }

        fclose($handle);
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

        if (!file_exists($backup->path)) {
            throw new Exception('Backup file not found on disk');
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

        if (file_exists($backup->path)) {
            @unlink($backup->path);
        }

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

            if (file_exists($backup->path)) {
                @unlink($backup->path);
            }

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
