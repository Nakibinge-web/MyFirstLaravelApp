@extends('layouts.app')

@section('page-title', 'Notifications')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Notifications</h1>
        <div class="flex flex-col sm:flex-row gap-2">
            <form action="{{ route('notifications.check') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="w-full sm:w-auto bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm sm:text-base">
                    Check for Alerts
                </button>
            </form>
            @if($unreadCount > 0)
                <button onclick="markAllAsRead()" class="w-full sm:w-auto bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm sm:text-base">
                    Mark All Read
                </button>
            @endif
        </div>
    </div>

    @if($unreadCount > 0)
        <div id="unreadCountBanner" class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            You have <span id="unreadCount">{{ $unreadCount }}</span> unread notification<span id="unreadPlural">{{ $unreadCount > 1 ? 's' : '' }}</span>
        </div>
    @endif

    @if($notifications->count() > 0)
        <div class="space-y-3">
            @foreach($notifications as $notification)
                @php
                    $bgColor = 'bg-white';
                    $borderColor = 'border-gray-200';
                    $iconBg = 'bg-gray-100';
                    
                    if ($notification->type === 'budget_exceeded') {
                        $borderColor = 'border-red-300';
                        $iconBg = 'bg-red-100';
                    } elseif ($notification->type === 'budget_warning') {
                        $borderColor = 'border-yellow-300';
                        $iconBg = 'bg-yellow-100';
                    } elseif ($notification->type === 'goal_achieved') {
                        $borderColor = 'border-green-300';
                        $iconBg = 'bg-green-100';
                    } elseif ($notification->type === 'goal_reminder') {
                        $borderColor = 'border-blue-300';
                        $iconBg = 'bg-blue-100';
                    }
                @endphp
                <div class="bg-white shadow-md rounded-lg p-3 sm:p-4 border-l-4 {{ $borderColor }} notification-item {{ $notification->is_read ? 'opacity-75' : '' }} transition-all hover:shadow-lg" data-notification-id="{{ $notification->id }}">
                    <div class="flex justify-between items-start gap-3">
                        <div class="flex items-start space-x-2 sm:space-x-3 flex-1 min-w-0">
                            <div class="text-2xl sm:text-3xl {{ $iconBg }} rounded-full w-10 h-10 sm:w-12 sm:h-12 flex items-center justify-center flex-shrink-0">
                                @if($notification->type === 'budget_exceeded')
                                    ⚠️
                                @elseif($notification->type === 'budget_warning')
                                    ⚡
                                @elseif($notification->type === 'goal_achieved')
                                    🎉
                                @elseif($notification->type === 'goal_reminder')
                                    ⏰
                                @else
                                    {{ $notification->icon ?? '🔔' }}
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-2 flex-wrap">
                                    <h3 class="font-semibold text-base sm:text-lg">{{ $notification->title }}</h3>
                                    @if(!$notification->is_read)
                                        <span class="new-badge bg-blue-500 text-white text-xs px-2 py-1 rounded animate-pulse">New</span>
                                    @endif
                                </div>
                                <p class="text-gray-600 mt-1 text-sm sm:text-base">{{ $notification->message }}</p>
                                <p class="text-xs sm:text-sm text-gray-500 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex flex-col sm:flex-row gap-2 flex-shrink-0">
                            @if(!$notification->is_read)
                                <button onclick="markAsRead({{ $notification->id }})" class="mark-read-btn text-blue-600 hover:text-blue-900 text-xs sm:text-sm font-medium whitespace-nowrap">Mark Read</button>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this notification?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-xs sm:text-sm font-medium whitespace-nowrap">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg px-6 py-12 text-center">
            <div class="text-6xl mb-4">🔔</div>
            <p class="text-lg text-gray-500 mb-4">No notifications yet</p>
            <p class="text-gray-600">We'll notify you about budget alerts and goal deadlines</p>
        </div>
    @endif
</div>

<script>
async function markAsRead(notificationId) {
    try {
        const response = await fetch(`/notifications/${notificationId}/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            const data = await response.json();
            
            // Find the notification element
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            
            if (notificationElement) {
                // Add opacity to show it's read
                notificationElement.classList.add('opacity-75');
                
                // Remove the "New" badge
                const newBadge = notificationElement.querySelector('.new-badge');
                if (newBadge) {
                    newBadge.remove();
                }
                
                // Remove the "Mark Read" button
                const markReadBtn = notificationElement.querySelector('.mark-read-btn');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            }
            
            // Update the unread count
            const unreadCountElement = document.getElementById('unreadCount');
            const unreadCountBanner = document.getElementById('unreadCountBanner');
            const unreadPlural = document.getElementById('unreadPlural');
            
            if (unreadCountElement) {
                let currentCount = parseInt(unreadCountElement.textContent);
                currentCount = Math.max(0, currentCount - 1);
                unreadCountElement.textContent = currentCount;
                
                // Update plural
                if (unreadPlural) {
                    unreadPlural.textContent = currentCount > 1 ? 's' : '';
                }
                
                // Hide banner if count is 0
                if (currentCount === 0 && unreadCountBanner) {
                    unreadCountBanner.style.display = 'none';
                }
            }
            
            // Update header notification badge if it exists
            if (typeof loadNotificationDropdown === 'function') {
                loadNotificationDropdown();
            }
            
            // Show success message (optional)
            if (window.Toast) {
                window.Toast.success('Notification marked as read');
            }
        } else {
            throw new Error('Failed to mark notification as read');
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
        if (window.Toast) {
            window.Toast.error('Failed to mark notification as read');
        }
    }
}

async function markAllAsRead() {
    try {
        const response = await fetch('/notifications/read-all', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            // Add opacity to all notification items
            const notificationElements = document.querySelectorAll('.notification-item');
            notificationElements.forEach(element => {
                element.classList.add('opacity-75');
                
                // Remove "New" badges
                const newBadge = element.querySelector('.new-badge');
                if (newBadge) {
                    newBadge.remove();
                }
                
                // Remove "Mark Read" buttons
                const markReadBtn = element.querySelector('.mark-read-btn');
                if (markReadBtn) {
                    markReadBtn.remove();
                }
            });
            
            // Hide the unread count banner
            const unreadCountBanner = document.getElementById('unreadCountBanner');
            if (unreadCountBanner) {
                unreadCountBanner.style.display = 'none';
            }
            
            // Update header notification badge if it exists
            if (typeof loadNotificationDropdown === 'function') {
                loadNotificationDropdown();
            }
            
            // Show success message
            if (window.Toast) {
                window.Toast.success('All notifications marked as read');
            }
        } else {
            throw new Error('Failed to mark all notifications as read');
        }
    } catch (error) {
        console.error('Error marking all notifications as read:', error);
        if (window.Toast) {
            window.Toast.error('Failed to mark all notifications as read');
        }
    }
}
</script>
@endsection
