@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Notifications</h1>
        <div class="flex space-x-2">
            <form action="{{ route('notifications.check') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Check for Alerts
                </button>
            </form>
            @if($unreadCount > 0)
                <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                        Mark All Read
                    </button>
                </form>
            @endif
        </div>
    </div>

    @if($unreadCount > 0)
        <div class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded mb-4">
            You have {{ $unreadCount }} unread notification{{ $unreadCount > 1 ? 's' : '' }}
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
                <div class="bg-white shadow-md rounded-lg p-4 border-l-4 {{ $borderColor }} {{ $notification->is_read ? 'opacity-75' : '' }} transition-all hover:shadow-lg">
                    <div class="flex justify-between items-start">
                        <div class="flex items-start space-x-3 flex-1">
                            <div class="text-3xl {{ $iconBg }} rounded-full w-12 h-12 flex items-center justify-center">
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
                            <div class="flex-1">
                                <div class="flex items-center space-x-2">
                                    <h3 class="font-semibold text-lg">{{ $notification->title }}</h3>
                                    @if(!$notification->is_read)
                                        <span class="bg-blue-500 text-white text-xs px-2 py-1 rounded animate-pulse">New</span>
                                    @endif
                                </div>
                                <p class="text-gray-600 mt-1">{{ $notification->message }}</p>
                                <p class="text-sm text-gray-500 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                        <div class="flex space-x-2 ml-4">
                            @if(!$notification->is_read)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 hover:text-blue-900 text-sm font-medium">Mark Read</button>
                                </form>
                            @endif
                            <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this notification?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 text-sm font-medium">Delete</button>
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
@endsection
