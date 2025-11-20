<?php

namespace App\Services;

use App\Models\Goal;
use Carbon\Carbon;

class GoalService
{
    public function updateProgress(Goal $goal, float $amount): Goal
    {
        $newAmount = $goal->current_amount + $amount;
        $goal->update(['current_amount' => $newAmount]);

        // Check if goal is completed
        if ($newAmount >= $goal->target_amount && $goal->status !== 'completed') {
            $goal->update(['status' => 'completed']);
        }

        return $goal->fresh();
    }

    public function calculateProgress(Goal $goal): array
    {
        $percentage = $goal->progress_percentage;
        $remaining = $goal->target_amount - $goal->current_amount;
        $daysRemaining = (int) Carbon::now()->diffInDays($goal->target_date, false);
        
        return [
            'percentage' => $percentage,
            'remaining' => max($remaining, 0),
            'days_remaining' => max($daysRemaining, 0),
            'is_overdue' => $daysRemaining < 0 && $goal->status !== 'completed',
            'is_completed' => $goal->isCompleted(),
        ];
    }

    public function getEstimatedCompletionDate(Goal $goal): ?Carbon
    {
        if ($goal->current_amount >= $goal->target_amount) {
            return null; // Already completed
        }

        $remaining = $goal->target_amount - $goal->current_amount;
        $daysElapsed = Carbon::parse($goal->created_at)->diffInDays(Carbon::now());
        
        if ($daysElapsed === 0 || $goal->current_amount === 0) {
            return null; // Not enough data
        }

        $dailyRate = $goal->current_amount / $daysElapsed;
        
        if ($dailyRate <= 0) {
            return null;
        }

        $daysNeeded = ceil($remaining / $dailyRate);
        
        return Carbon::now()->addDays($daysNeeded);
    }

    public function getActiveGoals($userId)
    {
        return Goal::where('user_id', $userId)
            ->where('status', 'active')
            ->orderBy('target_date', 'asc')
            ->get();
    }

    public function getCompletedGoals($userId)
    {
        return Goal::where('user_id', $userId)
            ->where('status', 'completed')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function checkDeadlines($userId): array
    {
        $goals = Goal::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $notifications = [];

        foreach ($goals as $goal) {
            $daysRemaining = (int) Carbon::now()->diffInDays($goal->target_date, false);
            
            if ($daysRemaining < 0) {
                $notifications[] = "Goal '{$goal->name}' is overdue!";
            } elseif ($daysRemaining <= 7) {
                $notifications[] = "Goal '{$goal->name}' deadline is approaching ({$daysRemaining} days left)!";
            }
        }

        return $notifications;
    }
}
