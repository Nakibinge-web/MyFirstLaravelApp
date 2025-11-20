<?php

namespace App\Http\Controllers;

use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $notifications = $this->notificationService->getAllNotifications(auth()->id(), 50);
        $unreadCount = $this->notificationService->getUnreadNotifications(auth()->id())->count();

        return view('notifications.index', compact('notifications', 'unreadCount'));
    }

    public function getUnread()
    {
        $notifications = $this->notificationService->getUnreadNotifications(auth()->id());
        
        return response()->json([
            'count' => $notifications->count(),
            'notifications' => $notifications->map(function ($n) {
                return [
                    'id' => $n->id,
                    'type' => $n->type,
                    'title' => $n->title,
                    'message' => $n->message,
                    'icon' => $n->icon,
                    'color' => $n->color,
                    'created_at' => $n->created_at->diffForHumans(),
                ];
            }),
        ]);
    }

    public function markAsRead($id)
    {
        $this->notificationService->markAsRead($id);
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        $this->notificationService->markAllAsRead(auth()->id());
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        $this->notificationService->deleteNotification($id);
        return redirect()->back()->with('success', 'Notification deleted.');
    }

    public function checkAlerts()
    {
        // Check budget alerts
        $this->notificationService->checkBudgetAlerts(auth()->id());
        
        // Check goal deadlines
        $this->notificationService->checkGoalDeadlines(auth()->id());

        return redirect()->back()->with('success', 'Notifications checked and updated.');
    }
}
