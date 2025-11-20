<?php

namespace Tests\Unit;

use App\Models\Budget;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Notification;
use App\Models\Transaction;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $notificationService;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notificationService = new NotificationService();
        $this->user = User::factory()->create();
    }

    public function test_can_create_notification(): void
    {
        $notification = $this->notificationService->createNotification(
            $this->user->id,
            'test_type',
            'Test Title',
            'Test Message',
            '🔔',
            'blue'
        );

        $this->assertInstanceOf(Notification::class, $notification);
        $this->assertEquals('Test Title', $notification->title);
        $this->assertEquals('Test Message', $notification->message);
        $this->assertFalse($notification->is_read);
    }

    public function test_budget_alert_for_exceeded_budget(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
        ]);

        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'amount' => 100,
            'period' => 'monthly',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        // Create transaction that exceeds budget
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 150,
            'date' => now(),
        ]);

        $notifications = $this->notificationService->checkBudgetAlerts($this->user->id);

        $this->assertCount(1, $notifications);
        $this->assertEquals('budget_alert', $notifications[0]->type);
        $this->assertEquals('Budget Exceeded!', $notifications[0]->title);
        $this->assertEquals('red', $notifications[0]->color);
    }

    public function test_budget_warning_at_80_percent(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
        ]);

        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'amount' => 100,
            'period' => 'monthly',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        // Create transaction at 85% of budget
        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 85,
            'date' => now(),
        ]);

        $notifications = $this->notificationService->checkBudgetAlerts($this->user->id);

        $this->assertCount(1, $notifications);
        $this->assertEquals('budget_alert', $notifications[0]->type);
        $this->assertEquals('Budget Warning', $notifications[0]->title);
        $this->assertEquals('yellow', $notifications[0]->color);
    }

    public function test_goal_deadline_reminder(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => now()->addDays(5),
            'status' => 'active',
            'current_amount' => 50,
            'target_amount' => 100,
        ]);

        $notifications = $this->notificationService->checkGoalDeadlines($this->user->id);

        $this->assertCount(1, $notifications);
        $this->assertEquals('goal_reminder', $notifications[0]->type);
        $this->assertEquals('Goal Deadline Approaching', $notifications[0]->title);
        $this->assertEquals('yellow', $notifications[0]->color);
    }

    public function test_goal_overdue_notification(): void
    {
        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'target_date' => now()->subDays(5),
            'status' => 'active',
            'current_amount' => 50,
            'target_amount' => 100,
        ]);

        $notifications = $this->notificationService->checkGoalDeadlines($this->user->id);

        $this->assertCount(1, $notifications);
        $this->assertEquals('goal_reminder', $notifications[0]->type);
        $this->assertEquals('Goal Overdue', $notifications[0]->title);
        $this->assertEquals('red', $notifications[0]->color);
    }

    public function test_goal_achievement_notification(): void
    {
        $notification = $this->notificationService->sendGoalAchievedNotification(
            $this->user->id,
            'Emergency Fund'
        );

        $this->assertEquals('goal_achieved', $notification->type);
        $this->assertEquals('Goal Achieved! 🎉', $notification->title);
        $this->assertEquals('green', $notification->color);
        $this->assertStringContainsString('Emergency Fund', $notification->message);
    }

    public function test_get_unread_notifications(): void
    {
        // Create read and unread notifications
        Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => true,
        ]);

        Notification::factory()->count(3)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $unread = $this->notificationService->getUnreadNotifications($this->user->id);

        $this->assertCount(3, $unread);
    }

    public function test_mark_notification_as_read(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $this->notificationService->markAsRead($notification->id);

        $notification->refresh();
        $this->assertTrue($notification->is_read);
        $this->assertNotNull($notification->read_at);
    }

    public function test_mark_all_as_read(): void
    {
        Notification::factory()->count(5)->create([
            'user_id' => $this->user->id,
            'is_read' => false,
        ]);

        $this->notificationService->markAllAsRead($this->user->id);

        $unread = Notification::where('user_id', $this->user->id)
            ->where('is_read', false)
            ->count();

        $this->assertEquals(0, $unread);
    }

    public function test_delete_notification(): void
    {
        $notification = Notification::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $result = $this->notificationService->deleteNotification($notification->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_prevents_duplicate_budget_alerts(): void
    {
        $category = Category::factory()->create([
            'user_id' => $this->user->id,
            'type' => 'expense',
        ]);

        $budget = Budget::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'amount' => 100,
            'period' => 'monthly',
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->endOfMonth(),
        ]);

        Transaction::factory()->create([
            'user_id' => $this->user->id,
            'category_id' => $category->id,
            'type' => 'expense',
            'amount' => 150,
            'date' => now(),
        ]);

        // First check - should create notification
        $notifications1 = $this->notificationService->checkBudgetAlerts($this->user->id);
        $this->assertCount(1, $notifications1);

        // Second check within 24 hours - should not create duplicate
        $notifications2 = $this->notificationService->checkBudgetAlerts($this->user->id);
        $this->assertCount(0, $notifications2);
    }
}
