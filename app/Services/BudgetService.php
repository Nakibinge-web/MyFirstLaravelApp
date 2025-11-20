<?php

namespace App\Services;

use App\Models\Budget;
use Carbon\Carbon;

class BudgetService
{
    public function calculateUtilization(Budget $budget): array
    {
        $spent = $budget->category->transactions()
            ->where('user_id', $budget->user_id)
            ->where('type', 'expense')
            ->whereBetween('date', [$budget->start_date, $budget->end_date])
            ->sum('amount');

        $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;
        $remaining = $budget->amount - $spent;

        return [
            'spent' => $spent,
            'percentage' => round($percentage, 2),
            'remaining' => $remaining,
            'status' => $this->getStatus($percentage),
        ];
    }

    public function getStatus(float $percentage): string
    {
        if ($percentage >= 100) {
            return 'exceeded';
        } elseif ($percentage >= 80) {
            return 'warning';
        } else {
            return 'good';
        }
    }

    public function checkBudgetLimits(Budget $budget): ?string
    {
        $utilization = $this->calculateUtilization($budget);
        
        if ($utilization['status'] === 'exceeded') {
            return "Budget exceeded for {$budget->category->name}! You've spent \${$utilization['spent']} of \${$budget->amount}.";
        } elseif ($utilization['status'] === 'warning') {
            return "Budget warning for {$budget->category->name}! You've used {$utilization['percentage']}% of your budget.";
        }

        return null;
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

    public function createMonthlyBudget(array $data): Budget
    {
        $startDate = Carbon::parse($data['start_date'] ?? now())->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();

        return Budget::create([
            'user_id' => $data['user_id'],
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'period' => 'monthly',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function createWeeklyBudget(array $data): Budget
    {
        $startDate = Carbon::parse($data['start_date'] ?? now())->startOfWeek();
        $endDate = $startDate->copy()->endOfWeek();

        return Budget::create([
            'user_id' => $data['user_id'],
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'period' => 'weekly',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }

    public function createYearlyBudget(array $data): Budget
    {
        $startDate = Carbon::parse($data['start_date'] ?? now())->startOfYear();
        $endDate = $startDate->copy()->endOfYear();

        return Budget::create([
            'user_id' => $data['user_id'],
            'category_id' => $data['category_id'],
            'amount' => $data['amount'],
            'period' => 'yearly',
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
    }
}
