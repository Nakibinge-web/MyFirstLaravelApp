<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public function createTransaction(array $data): Transaction
    {
        return DB::transaction(function () use ($data) {
            $transaction = Transaction::create($data);
            
            // Update budget utilization if expense
            if ($transaction->type === 'expense') {
                $notificationCreated = $this->updateBudgetUtilization($transaction);
                
                // Store notification flag in session for frontend
                if ($notificationCreated) {
                    session()->flash('budget_notification', $notificationCreated);
                }
            }
            
            return $transaction;
        });
    }

    public function updateTransaction(Transaction $transaction, array $data): Transaction
    {
        DB::transaction(function () use ($transaction, $data) {
            $transaction->update($data);
            
            // Update budget utilization if expense
            if ($transaction->type === 'expense') {
                $this->updateBudgetUtilization($transaction);
            }
        });
        
        return $transaction->fresh();
    }

    public function deleteTransaction(Transaction $transaction): bool
    {
        return $transaction->delete();
    }

    protected function updateBudgetUtilization(Transaction $transaction): ?array
    {
        $notificationData = null;
        
        // Find active budgets for this category
        $budgets = \App\Models\Budget::where('user_id', $transaction->user_id)
            ->where('category_id', $transaction->category_id)
            ->where('start_date', '<=', $transaction->date)
            ->where('end_date', '>=', $transaction->date)
            ->get();

        foreach ($budgets as $budget) {
            // Calculate current utilization
            $spent = $budget->category->transactions()
                ->where('user_id', $budget->user_id)
                ->where('type', 'expense')
                ->whereBetween('date', [$budget->start_date, $budget->end_date])
                ->sum('amount');

            $percentage = $budget->amount > 0 ? ($spent / $budget->amount) * 100 : 0;

            // Check if budget is exceeded
            if ($percentage >= 100) {
                // Check if notification already exists for this budget
                $existingNotification = \App\Models\Notification::where('user_id', $transaction->user_id)
                    ->where('type', 'budget_exceeded')
                    ->where('title', 'like', '%' . $budget->category->name . '%')
                    ->where('created_at', '>=', $budget->start_date)
                    ->where('created_at', '<=', $budget->end_date)
                    ->first();

                if (!$existingNotification) {
                    $message = "You have exceeded your {$budget->period} budget for {$budget->category->name}. You've spent " . currency_format($spent) . " of your " . currency_format($budget->amount) . " budget (" . round($percentage, 1) . "%).";
                    
                    // Create notification
                    \App\Models\Notification::create([
                        'user_id' => $transaction->user_id,
                        'type' => 'budget_exceeded',
                        'title' => 'Budget Exceeded: ' . $budget->category->name,
                        'message' => $message,
                        'is_read' => false,
                    ]);
                    
                    $notificationData = [
                        'type' => 'exceeded',
                        'title' => 'Budget Exceeded!',
                        'message' => $message,
                        'category' => $budget->category->name,
                    ];
                }
            } elseif ($percentage >= 80 && $percentage < 100) {
                // Check if warning notification already exists
                $existingWarning = \App\Models\Notification::where('user_id', $transaction->user_id)
                    ->where('type', 'budget_warning')
                    ->where('title', 'like', '%' . $budget->category->name . '%')
                    ->where('created_at', '>=', $budget->start_date)
                    ->where('created_at', '<=', $budget->end_date)
                    ->first();

                if (!$existingWarning) {
                    $message = "You are approaching your {$budget->period} budget limit for {$budget->category->name}. You've spent " . currency_format($spent) . " of your " . currency_format($budget->amount) . " budget (" . round($percentage, 1) . "%).";
                    
                    // Create warning notification
                    \App\Models\Notification::create([
                        'user_id' => $transaction->user_id,
                        'type' => 'budget_warning',
                        'title' => 'Budget Warning: ' . $budget->category->name,
                        'message' => $message,
                        'is_read' => false,
                    ]);
                    
                    $notificationData = [
                        'type' => 'warning',
                        'title' => 'Budget Warning',
                        'message' => $message,
                        'category' => $budget->category->name,
                    ];
                }
            }
        }
        
        return $notificationData;
    }
}
