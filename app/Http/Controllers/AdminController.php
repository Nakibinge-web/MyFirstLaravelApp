<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use App\Models\SystemSetting;
use App\Services\Admin\ActivityLogService;
use App\Services\Admin\BackupService;
use App\Services\Admin\DashboardService;
use App\Services\Admin\UserManagementService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboardService,
        private readonly ActivityLogService $activityLogService,
        private readonly BackupService $backupService,
        private readonly UserManagementService $userManagementService,
    ) {}

    // -------------------------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------------------------

    /**
     * Display the admin dashboard with system metrics.
     *
     * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8
     */
    public function dashboard(): View
    {
        $metrics = $this->dashboardService->getSystemMetrics();

        return view('admin.dashboard', compact('metrics'));
    }

    // -------------------------------------------------------------------------
    // User Management
    // -------------------------------------------------------------------------

    /**
     * Display a paginated list of all users.
     *
     * Requirements: 7.1, 12.7
     */
    public function users(Request $request): View
    {
        $request->validate([
            'search'    => ['nullable', 'string', 'max:100'],
            'is_admin'  => ['nullable', 'in:0,1'],
            'is_active' => ['nullable', 'in:0,1'],
        ]);

        $filters = $request->only(['search', 'is_admin', 'is_active']);
        $users   = $this->userManagementService->getAllUsers($filters);

        return view('admin.users.index', compact('users', 'filters'));
    }

    /**
     * Display details and statistics for a specific user.
     *
     * Requirements: 7.2, 7.3, 7.4, 8.1-8.9
     */
    public function userShow(int $userId): View
    {
        $details        = $this->userManagementService->getUserDetails($userId);
        $user           = $details['user'];
        $statistics     = $details['statistics'];
        $recentActivity = $this->activityLogService->getUserActivity($userId, 20);

        return view('admin.users.show', compact('user', 'statistics', 'recentActivity'));
    }

    /**
     * Toggle a user's active status (activate / deactivate).
     *
     * Requirements: 7.5, 7.6, 7.7, 14.3
     */
    public function userToggleStatus(int $userId): RedirectResponse
    {
        try {
            $user   = $this->userManagementService->toggleUserStatus($userId);
            $status = $user->is_active ? 'activated' : 'deactivated';

            return redirect()->route('admin.users')
                ->with('success', "User {$user->name} has been {$status}.");
        } catch (Exception $e) {
            return redirect()->route('admin.users')
                ->with('error', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // Activity Logs
    // -------------------------------------------------------------------------

    /**
     * Display activity logs with optional filtering.
     *
     * Requirements: 4.1, 4.2, 4.3, 4.4, 4.5, 4.6, 4.7, 4.8
     */
    public function activityLogs(Request $request): View
    {
        $request->validate([
            'user_id'   => ['nullable', 'integer', 'min:1'],
            'action'    => ['nullable', 'string', 'max:50'],
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['nullable', 'date', 'after_or_equal:date_from'],
            'search'    => ['nullable', 'string', 'max:200'],
        ]);

        $filters = $request->only(['user_id', 'action', 'date_from', 'date_to', 'search']);
        $logs    = $this->activityLogService->getActivityLogs($filters);
        $actions = $this->activityLogService->getActionTypes();

        return view('admin.activity-logs.index', compact('logs', 'filters', 'actions'));
    }

    // -------------------------------------------------------------------------
    // Backups
    // -------------------------------------------------------------------------

    /**
     * Display the list of all backups.
     *
     * Requirements: 6.1
     */
    public function backups(): View
    {
        $backups = $this->backupService->listBackups();

        return view('admin.backups.index', compact('backups'));
    }

    /**
     * Trigger creation of a new database backup.
     *
     * Requirements: 5.1-5.9, 14.1
     */
    public function createBackup(Request $request): RedirectResponse
    {
        $request->validate([
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $backup = $this->backupService->createBackup(
                $request->input('description', 'Manual backup')
            );

            return redirect()->route('admin.backups')
                ->with('success', "Backup created successfully: {$backup->filename}");
        } catch (Exception $e) {
            return redirect()->route('admin.backups')
                ->with('error', "Backup failed: {$e->getMessage()}");
        }
    }

    /**
     * Serve a backup file for download.
     *
     * Requirements: 6.2, 6.3, 6.7, 14.2
     */
    public function downloadBackup(int $backupId): Response|RedirectResponse
    {
        $backup = Backup::findOrFail($backupId);

        if (!$backup->exists()) {
            abort(404, 'Backup file not found');
        }

        $this->activityLogService->log(
            'backup_downloaded',
            "Backup downloaded: {$backup->filename}",
            auth()->id(),
            ['backup_id' => $backupId]
        );

        return response()->download($backup->path, $backup->filename);
    }

    /**
     * Delete a backup file and its database record.
     *
     * Requirements: 6.4, 6.5
     */
    public function deleteBackup(int $backupId): RedirectResponse
    {
        try {
            $this->backupService->deleteBackup($backupId);

            return redirect()->route('admin.backups')
                ->with('success', 'Backup deleted successfully.');
        } catch (Exception $e) {
            return redirect()->route('admin.backups')
                ->with('error', "Failed to delete backup: {$e->getMessage()}");
        }
    }

    // -------------------------------------------------------------------------
    // System Settings
    // -------------------------------------------------------------------------

    /**
     * Display all system settings.
     *
     * Requirements: 10.1
     */
    public function systemSettings(): View
    {
        $settings = SystemSetting::orderBy('key')->get();

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Save updated system settings.
     *
     * Requirements: 10.2, 10.3, 10.4, 10.5, 10.6
     */
    public function updateSystemSettings(Request $request): RedirectResponse
    {
        $settings = SystemSetting::all()->keyBy('key');

        foreach ($request->input('settings', []) as $key => $value) {
            /** @var SystemSetting|null $setting */
            $setting = $settings->get($key);

            if (!$setting) {
                continue;
            }

            // Validate value according to the setting's type
            $validated = $this->validateSettingValue($value, $setting->type);

            if ($validated === null) {
                return redirect()->route('admin.settings')
                    ->with('error', "Invalid value for setting '{$key}' (expected type: {$setting->type}).");
            }

            $oldValue = $setting->value;
            SystemSetting::set($key, $validated, $setting->type);

            // Log the change
            $this->activityLogService->log(
                'setting_updated',
                "System setting '{$key}' updated",
                auth()->id(),
                ['key' => $key, 'old_value' => $oldValue, 'new_value' => (string) $validated]
            );
        }

        return redirect()->route('admin.settings')
            ->with('success', 'System settings updated successfully.');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Validate and cast a raw string value to the appropriate type.
     *
     * Returns the cast value on success, or null if validation fails.
     */
    private function validateSettingValue(mixed $value, string $type): mixed
    {
        return match ($type) {
            'integer' => is_numeric($value) ? (int) $value : null,
            'boolean' => in_array(strtolower((string) $value), ['1', '0', 'true', 'false', 'yes', 'no'], true)
                ? filter_var($value, FILTER_VALIDATE_BOOLEAN)
                : null,
            'json'    => $this->validateJson($value),
            default   => (string) $value,   // 'string' – always valid
        };
    }

    /**
     * Validate a JSON string and return the decoded value, or null on failure.
     */
    private function validateJson(mixed $value): mixed
    {
        if (!is_string($value)) {
            return null;
        }

        $decoded = json_decode($value, true);

        return json_last_error() === JSON_ERROR_NONE ? $decoded : null;
    }
}
