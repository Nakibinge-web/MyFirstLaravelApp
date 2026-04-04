<?php

namespace App\Services\Admin;

use App\Models\ActivityLog;
use App\Models\Backup;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    /**
     * Get all system metrics, cached for 5 minutes.
     *
     * Requirements: 2.1, 2.2, 2.3, 2.4, 2.5, 2.6, 2.7, 2.8, 12.1
     */
    public function getSystemMetrics(): array
    {
        return Cache::remember('admin.dashboard.metrics', 300, function () {
            // Step 1: User statistics
            $totalUsers        = $this->getTotalUsers();
            $activeUsers       = $this->getActiveUsers(30);
            $newUsersThisMonth = User::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            // Step 2: Transaction statistics
            $totalTransactions     = $this->getTotalTransactions();
            $transactionVolume     = $this->getTransactionVolume();
            $transactionsThisMonth = Transaction::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count();

            // Step 3: Financial statistics
            $totalIncome   = (float) Transaction::where('type', 'income')->sum('amount');
            $totalExpenses = (float) Transaction::where('type', 'expense')->sum('amount');

            // Step 4: Recent activity
            $recentActivity = $this->getRecentActivity(10);

            // Step 5: System health
            $databaseSize = $this->getDatabaseSize();
            $backupCount  = Backup::where('status', 'completed')->count();
            $lastBackup   = Backup::where('status', 'completed')
                ->orderBy('created_at', 'desc')
                ->first();

            return [
                'users' => [
                    'total'          => $totalUsers,
                    'active'         => $activeUsers,
                    'new_this_month' => $newUsersThisMonth,
                ],
                'transactions' => [
                    'total'      => $totalTransactions,
                    'volume'     => $transactionVolume,
                    'this_month' => $transactionsThisMonth,
                ],
                'financial' => [
                    'total_income'   => $totalIncome,
                    'total_expenses' => $totalExpenses,
                    'net'            => $totalIncome - $totalExpenses,
                ],
                'system' => [
                    'database_size' => $databaseSize,
                    'backup_count'  => $backupCount,
                    'last_backup'   => $lastBackup,
                ],
                'recent_activity' => $recentActivity,
            ];
        });
    }

    /**
     * Get the total count of all users.
     *
     * Requirement: 2.1
     */
    public function getTotalUsers(): int
    {
        return User::count();
    }

    /**
     * Get the count of users who logged in within the given number of days.
     *
     * Requirement: 2.2
     */
    public function getActiveUsers(int $days = 30): int
    {
        return User::where('last_login_at', '>=', now()->subDays($days))->count();
    }

    /**
     * Get the total count of all transactions.
     *
     * Requirement: 2.3
     */
    public function getTotalTransactions(): int
    {
        return Transaction::count();
    }

    /**
     * Get the sum of all transaction amounts.
     *
     * Requirement: 2.4
     */
    public function getTransactionVolume(): float
    {
        return (float) Transaction::sum('amount');
    }

    /**
     * Get the most recent activity log entries.
     *
     * Requirement: 2.6
     */
    public function getRecentActivity(int $limit = 10): Collection
    {
        return ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get monthly user registration counts for the past N months.
     *
     * Requirement: 2.7
     */
    public function getUserGrowthData(int $months = 6): array
    {
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date  = now()->subMonths($i);
            $count = User::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month)
                ->count();

            $data[] = [
                'month' => $date->format('Y-m'),
                'label' => $date->format('M Y'),
                'count' => $count,
            ];
        }

        return $data;
    }

    /**
     * Get monthly transaction counts and volumes for the past N months.
     *
     * Requirement: 2.8
     */
    public function getTransactionTrends(int $months = 6): array
    {
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date   = now()->subMonths($i);
            $query  = Transaction::whereYear('created_at', $date->year)
                ->whereMonth('created_at', $date->month);

            $data[] = [
                'month'  => $date->format('Y-m'),
                'label'  => $date->format('M Y'),
                'count'  => (clone $query)->count(),
                'volume' => (float) (clone $query)->sum('amount'),
            ];
        }

        return $data;
    }

    /**
     * Get the SQLite database file size in bytes.
     * Returns 0 if the file does not exist.
     */
    private function getDatabaseSize(): int
    {
        $path = database_path('database.sqlite');

        if (!file_exists($path)) {
            return 0;
        }

        return (int) filesize($path);
    }
}
