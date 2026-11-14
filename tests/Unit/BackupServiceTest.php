<?php

namespace Tests\Unit;

use App\Models\Backup;
use App\Models\User;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\BackupService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class BackupServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BackupService $backupService;
    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupService = app(BackupService::class);
        $this->adminUser = User::factory()->create([
            'is_admin' => true,
        ]);
        $this->actingAs($this->adminUser);
    }

    public function test_can_create_backup(): void
    {
        $backup = $this->backupService->createBackup('Automated test backup');

        $this->assertInstanceOf(Backup::class, $backup);
        $this->assertEquals('completed', $backup->status);
        $this->assertFileExists($backup->path);
        $this->assertGreaterThan(0, $backup->size);

        // Clean up created file
        if (file_exists($backup->path)) {
            @unlink($backup->path);
        }
    }

    public function test_can_download_backup(): void
    {
        $backup = $this->backupService->createBackup('Download test backup');
        $path = $this->backupService->downloadBackup($backup->id);

        $this->assertEquals($backup->path, $path);
        $this->assertFileExists($path);

        // Clean up
        if (file_exists($backup->path)) {
            @unlink($backup->path);
        }
    }

    public function test_download_backup_throws_exception_if_file_missing(): void
    {
        $backup = Backup::create([
            'filename' => 'missing_file.sqlite',
            'path' => storage_path('app/backups/non_existent_file.sqlite'),
            'size' => 1024,
            'description' => 'Missing file test',
            'created_by' => $this->adminUser->id,
            'status' => 'completed',
        ]);

        $this->expectException(Exception::class);
        $this->backupService->downloadBackup($backup->id);
    }

    public function test_can_delete_backup_and_removes_file_from_disk(): void
    {
        $backup = $this->backupService->createBackup('Deletion test backup');
        $filePath = $backup->path;

        $this->assertFileExists($filePath);

        $result = $this->backupService->deleteBackup($backup->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('backups', ['id' => $backup->id]);
        $this->assertFileDoesNotExist($filePath);
    }

    public function test_cleanup_old_backups_prunes_excess_and_deletes_files(): void
    {
        $createdBackups = [];
        for ($i = 0; $i < 4; $i++) {
            $createdBackups[] = $this->backupService->createBackup("Backup {$i}");
            usleep(20000); // 20ms to ensure different timestamps
        }

        $deletedCount = $this->backupService->cleanupOldBackups(2);

        $this->assertEquals(2, $deletedCount);
        $this->assertEquals(2, Backup::where('status', 'completed')->count());

        // Cleanup remaining files
        foreach (Backup::all() as $b) {
            if (file_exists($b->path)) {
                @unlink($b->path);
            }
        }
    }
}
