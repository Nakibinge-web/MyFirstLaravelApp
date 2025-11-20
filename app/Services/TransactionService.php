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
                $this->updateBudgetUtilization($transaction);
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

    protected function updateBudgetUtilization(Transaction $transaction): void
    {
        // This will be implemented when we build the budget system
        // For now, it's a placeholder
    }
}
