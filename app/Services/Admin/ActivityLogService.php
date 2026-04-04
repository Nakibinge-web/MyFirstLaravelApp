<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ActivityLogService
{
    /**
     * Log an activity.
     */
    public function log(string $action, string $description, ?int $userId = null, ?array $metadata = null): void
    {
        ActivityLog::create([
            'action'      => $action,
            'description' => $description,
            'user_id'     => $userId,
            'ip_address'  => request()->ip(),
            'user_agent'  => request()->userAgent(),
            'metadata'    => $metadata,
        ]);
    }

    /**
     * Retrieve paginated activity logs with optional filters.
     *
     * Supported filters: user_id, action, date_from, date_to, search
     */
    public function getActivityLogs(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (!empty($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (!empty($filters['search'])) {
            $query->where('description', 'like', '%' . $filters['search'] . '%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Get activity logs for a specific user.
     */
    public function getUserActivity(int $userId, int $limit = 50): Collection
    {
        return ActivityLog::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get all distinct action types from the activity_logs table.
     */
    public function getActionTypes(): array
    {
        return ActivityLog::distinct()
            ->orderBy('action')
            ->pluck('action')
            ->toArray();
    }

    /**
     * Delete logs older than the given number of days.
     *
     * @return int Number of deleted records
     */
    public function deleteOldLogs(int $daysToKeep = 90): int
    {
        return ActivityLog::where('created_at', '<', now()->subDays($daysToKeep))->delete();
    }
}
