<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Goal;
use App\Models\Notification;
use Carbon\Carbon;

class NotificationService
{
    public function createNotification($userId, $type, $title, $message, $icon = null, $color = 'blue')
    {
        return Notification::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'icon' => $icon,
            'color' => $color,
        ]);
    }

    public function checkBudgetAlerts($userId)
    {
        $budgets = Budget::where('user_id', $userId)
            ->where('start_date', '<=', Carbon::now())
            ->where('end_date', '>=', Carbon::now())
            ->with('category')
            ->get();

        $budgetService = app(BudgetService::class);
        $notifications = [];

        foreach ($budgets as $budget) {
            $utilization = $budgetService->calculateUtilization($budget);
            
            // Check if we already sent a notification for this budget recently
            $recentNotification = Notification::where('user_id', $userId)
                ->where('type', 'budget_alert')
                ->where('message', 'like', '%' . $budget->category->name . '%')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if ($recentNotification) {
                continue;
            }

            if ($utilization['status'] === 'exceeded') {
                $notification = $this->createNotification(
                    $userId,
                    'budget_alert',
                    'Budget Exceeded!',
                    "Your {$budget->category->name} budget has been exceeded. You've spent \${$utilization['spent']} of \${$budget->amount}.",
                    '⚠️',
                    'red'
                );
                $notifications[] = $notification;
            } elseif ($utilization['status'] === 'warning') {
                $notification = $this->createNotification(
                    $userId,
                    'budget_alert',
                    'Budget Warning',
                    "You've used {$utilization['percentage']}% of your {$budget->category->name} budget.",
                    '⚠️',
                    'yellow'
                );
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    public function checkGoalDeadlines($userId)
    {
        $goals = Goal::where('user_id', $userId)
            ->where('status', 'active')
            ->get();

        $goalService = app(GoalService::class);
        $notifications = [];

        foreach ($goals as $goal) {
            $progress = $goalService->calculateProgress($goal);
            
            // Check if we already sent a notification for this goal recently
            $recentNotification = Notification::where('user_id', $userId)
                ->where('type', 'goal_reminder')
                ->where('message', 'like', '%' . $goal->name . '%')
                ->where('created_at', '>=', Carbon::now()->subHours(24))
                ->exists();

            if ($recentNotification) {
                continue;
            }

            if ($progress['is_overdue']) {
                $notification = $this->createNotification(
                    $userId,
                    'goal_reminder',
                    'Goal Overdue',
                    "Your goal '{$goal->name}' is overdue! You're at {$progress['percentage']}% completion.",
                    '⏰',
                    'red'
                );
                $notifications[] = $notification;
            } elseif ($progress['days_remaining'] <= 7 && $progress['days_remaining'] > 0) {
                $notification = $this->createNotification(
                    $userId,
                    'goal_reminder',
                    'Goal Deadline Approaching',
                    "Your goal '{$goal->name}' is due in {$progress['days_remaining']} days. You're at {$progress['percentage']}% completion.",
                    '⏰',
                    'yellow'
                );
                $notifications[] = $notification;
            }
        }

        return $notifications;
    }

    public function sendGoalAchievedNotification($userId, $goalName)
    {
        return $this->createNotification(
            $userId,
            'goal_achieved',
            'Goal Achieved! 🎉',
            "Congratulations! You've completed your goal: {$goalName}",
            '🎉',
            'green'
        );
    }

    public function getUnreadNotifications($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getAllNotifications($userId, $limit = 20)
    {
        return Notification::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->markAsRead();
        }
        return $notification;
    }

    public function markAllAsRead($userId)
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    public function deleteNotification($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification) {
            $notification->delete();
            return true;
        }
        return false;
    }
}
