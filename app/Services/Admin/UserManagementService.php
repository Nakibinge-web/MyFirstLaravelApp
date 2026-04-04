<?php

namespace App\Services\Admin;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class UserManagementService
{
    public function __construct(
        private readonly ActivityLogService $activityLogService
    ) {}

    /**
     * Get all users with optional filtering and pagination.
     *
     * Supported filters: search, is_admin, is_active
     */
    public function getAllUsers(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = User::query()->orderBy('created_at', 'desc');

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_admin'])) {
            $query->where('is_admin', (bool) $filters['is_admin']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Get detailed information for a specific user, including eager-loaded relationships.
     */
    public function getUserDetails(int $userId): array
    {
        $user = User::with(['transactions', 'budgets', 'goals', 'categories'])
            ->findOrFail($userId);

        return [
            'user'       => $user,
            'statistics' => $this->getUserStatistics($userId),
        ];
    }

    /**
     * Calculate statistics for a specific user.
     */
    public function getUserStatistics(int $userId): array
    {
        $user = User::findOrFail($userId);

        // Transaction statistics
        $totalTransactions = $user->transactions()->count();
        $totalIncome       = (float) $user->transactions()->where('type', 'income')->sum('amount');
        $totalExpenses     = (float) $user->transactions()->where('type', 'expense')->sum('amount');

        // Budget statistics
        $totalBudgets  = $user->budgets()->count();
        $activeBudgets = $user->budgets()->where('end_date', '>=', now())->count();

        // Goal statistics
        $totalGoals     = $user->goals()->count();
        $completedGoals = $user->goals()->where('status', 'completed')->count();

        // Category statistics
        $totalCategories = $user->categories()->count();

        // Account age
        $accountAge = (int) now()->diffInDays($user->created_at);

        return [
            'transactions' => [
                'total'    => $totalTransactions,
                'income'   => $totalIncome,
                'expenses' => $totalExpenses,
                'net'      => $totalIncome - $totalExpenses,
            ],
            'budgets' => [
                'total'  => $totalBudgets,
                'active' => $activeBudgets,
            ],
            'goals' => [
                'total'           => $totalGoals,
                'completed'       => $completedGoals,
                'completion_rate' => $totalGoals > 0 ? ($completedGoals / $totalGoals) * 100 : 0,
            ],
            'categories'       => $totalCategories,
            'account_age_days' => $accountAge,
            'last_login'       => $user->last_login_at,
        ];
    }

    /**
     * Toggle a user's is_active status.
     *
     * @throws \Exception if the admin attempts to deactivate their own account
     */
    public function toggleUserStatus(int $userId): User
    {
        $user = User::findOrFail($userId);

        if ($user->id === auth()->id()) {
            throw new \Exception('Cannot disable your own account');
        }

        $newStatus = ! $user->is_active;
        $user->update(['is_active' => $newStatus]);

        $action      = $newStatus ? 'user_activated' : 'user_deactivated';
        $description = $newStatus
            ? "User {$user->name} ({$user->email}) was activated"
            : "User {$user->name} ({$user->email}) was deactivated";

        $this->activityLogService->log(
            $action,
            $description,
            auth()->id(),
            ['target_user_id' => $userId, 'new_status' => $newStatus]
        );

        return $user;
    }

    /**
     * Delete a user and their associated data.
     */
    public function deleteUser(int $userId): bool
    {
        $user = User::findOrFail($userId);

        // Cascade-delete related records before removing the user
        $user->transactions()->delete();
        $user->budgets()->delete();
        $user->goals()->delete();
        $user->categories()->delete();

        return $user->delete();
    }

    /**
     * Promote a user to admin by setting is_admin = true.
     */
    public function promoteToAdmin(int $userId): User
    {
        $user = User::findOrFail($userId);
        $user->update(['is_admin' => true]);

        $this->activityLogService->log(
            'admin_promoted',
            "User {$user->name} ({$user->email}) was promoted to admin",
            auth()->id(),
            ['target_user_id' => $userId]
        );

        return $user;
    }

    /**
     * Revoke admin privileges from a user by setting is_admin = false.
     */
    public function revokeAdmin(int $userId): User
    {
        $user = User::findOrFail($userId);
        $user->update(['is_admin' => false]);

        $this->activityLogService->log(
            'admin_revoked',
            "Admin privileges revoked from user {$user->name} ({$user->email})",
            auth()->id(),
            ['target_user_id' => $userId]
        );

        return $user;
    }
}
