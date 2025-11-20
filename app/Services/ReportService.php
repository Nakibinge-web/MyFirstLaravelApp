<?php

namespace App\Services;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getMonthlyReport($userId, $year, $month)
    {
        $startDate = Carbon::create($year, $month, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

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
            'period' => $startDate->format('F Y'),
            'income' => $income,
            'expenses' => $expenses,
            'net_savings' => $netSavings,
            'savings_rate' => round($savingsRate, 2),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    public function getCategoryBreakdown($userId, $startDate, $endDate, $type = 'expense')
    {
        return Transaction::where('user_id', $userId)
            ->where('type', $type)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('category_id', DB::raw('SUM(amount) as total'))
            ->groupBy('category_id')
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name,
                    'color' => $item->category->color,
                    'icon' => $item->category->icon,
                    'amount' => $item->total,
                ];
            });
    }

    public function getIncomeVsExpenseTrend($userId, $months = 6)
    {
        $data = [];
        $startDate = Carbon::now()->subMonths($months - 1)->startOfMonth();

        for ($i = 0; $i < $months; $i++) {
            $monthStart = $startDate->copy()->addMonths($i);
            $monthEnd = $monthStart->copy()->endOfMonth();

            $income = Transaction::where('user_id', $userId)
                ->where('type', 'income')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');

            $expenses = Transaction::where('user_id', $userId)
                ->where('type', 'expense')
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');

            $data[] = [
                'month' => $monthStart->format('M Y'),
                'income' => $income,
                'expenses' => $expenses,
                'net' => $income - $expenses,
            ];
        }

        return $data;
    }

    public function getYearlyReport($userId, $year)
    {
        $startDate = Carbon::create($year, 1, 1)->startOfYear();
        $endDate = $startDate->copy()->endOfYear();

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
            'period' => $year,
            'income' => $income,
            'expenses' => $expenses,
            'net_savings' => $netSavings,
            'savings_rate' => round($savingsRate, 2),
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
    }

    public function getTopExpenseCategories($userId, $startDate, $endDate, $limit = 5)
    {
        return Transaction::where('user_id', $userId)
            ->where('type', 'expense')
            ->whereBetween('date', [$startDate, $endDate])
            ->select('category_id', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category_id')
            ->orderBy('total', 'desc')
            ->limit($limit)
            ->with('category')
            ->get()
            ->map(function ($item) {
                return [
                    'category' => $item->category->name,
                    'color' => $item->category->color,
                    'icon' => $item->category->icon,
                    'amount' => $item->total,
                    'count' => $item->count,
                ];
            });
    }

    public function getTransactionStats($userId, $startDate, $endDate)
    {
        $transactions = Transaction::where('user_id', $userId)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $incomeTransactions = $transactions->where('type', 'income');
        $expenseTransactions = $transactions->where('type', 'expense');

        return [
            'total_transactions' => $transactions->count(),
            'income_transactions' => $incomeTransactions->count(),
            'expense_transactions' => $expenseTransactions->count(),
            'avg_income' => $incomeTransactions->count() > 0 ? $incomeTransactions->avg('amount') : 0,
            'avg_expense' => $expenseTransactions->count() > 0 ? $expenseTransactions->avg('amount') : 0,
            'largest_income' => $incomeTransactions->max('amount') ?? 0,
            'largest_expense' => $expenseTransactions->max('amount') ?? 0,
        ];
    }
}
