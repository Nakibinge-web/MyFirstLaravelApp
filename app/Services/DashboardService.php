<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Goal;
use App\Models\Transaction;
use Carbon\Carbon;

class DashboardService
{
    public function getMonthlyStats($userId)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $income = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $expenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $netSavings = $income - $expenses;
        $savingsRate = $income > 0 ? ($netSavings / $income) * 100 : 0;

        return [
            'income' => $income,
            'expenses' => $expenses,
            'net_savings' => $netSavings,
            'savings_rate' => round($savingsRate, 2),
        ];
    }

    public function getRecentTransactions($userId, $limit = 10)
    {
        return Transaction::where('user_id', $userId)
            ->with('category')
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getActiveBudgets($userId)
    {
        $now = Carbon::now();
        
        return Budget::where('user_id', $userId)
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->with('category')
            ->get();
    }

    public function getActiveGoals($userId, $limit = 5)
    {
        return Goal::where('user_id', $userId)
            ->where('status', 'active')
            ->orderBy('target_date', 'asc')
            ->limit($limit)
            ->get();
    }

    public function calculateNetWorth($userId)
    {
        // Simple calculation: Total income - Total expenses (all time)
        $totalIncome = Transaction::where('user_id', $userId)
            ->where('type', 'income')
            ->sum('amount');

        $totalExpenses = Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->sum('amount');

        return $totalIncome - $totalExpenses;
    }

    public function getQuickStats($userId)
    {
        $totalTransactions = Transaction::where('user_id', $userId)->count();
        $totalBudgets = Budget::where('user_id', $userId)->count();
        $totalGoals = Goal::where('user_id', $userId)->count();
        $activeGoals = Goal::where('user_id', $userId)->where('status', 'active')->count();

        return [
            'total_transactions' => $totalTransactions,
            'total_budgets' => $totalBudgets,
            'total_goals' => $totalGoals,
            'active_goals' => $activeGoals,
        ];
    }

    public function getSpendingTrend($userId, $days = 7)
    {
        $data = [];
        $startDate = Carbon::now()->subDays($days - 1);

        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            
            $expenses = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereDate('date', $date)
                ->sum('amount');

            $data[] = [
                'date' => $date->format('M d'),
                'amount' => $expenses,
            ];
        }

        return $data;
    }
}
