<!-- Notification Dropdown -->
<div class="relative" x-data="{ open: false }">
    <button @click="open = !open" class="relative text-gray-500 hover:text-gray-700 focus:outline-none" title="Notifications">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <span id="headerNotificationBadge" class="hidden absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center badge-pulse font-semibold"></span>
    </button>

    <!-- Dropdown Menu -->
    <div x-show="open" 
         @click.away="open = false"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 transform scale-95"
         x-transition:enter-end="opacity-100 transform scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 transform scale-100"
         x-transition:leave-end="opacity-0 transform scale-95"
         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50"
         style="display: none;">
        
        <!-- Header -->
        <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
            <a href="{{ route('notifications.index') }}" class="text-xs text-blue-600 hover:text-blue-800">View All</a>
        </div>

        <!-- Notifications List -->
        <div id="notificationDropdownList" class="max-h-96 overflow-y-auto">
            <div class="px-4 py-8 text-center text-gray-500">
                <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                </svg>
                <p class="text-sm">No new notifications</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="px-4 py-3 border-t border-gray-200 text-center">
            <form action="{{ route('notifications.read-all') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                    Mark All as Read
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    // Load notifications into dropdown
    async function loadNotificationDropdown() {
        try {
            const response = await fetch('{{ route('notifications.unread') }}');
            const data = await response.json();
            
            const list = document.getElementById('notificationDropdownList');
            const headerBadge = document.getElementById('headerNotificationBadge');
            
            // Update badge
            if (data.count > 0) {
                headerBadge.textContent = data.count > 9 ? '9+' : data.count;
                headerBadge.classList.remove('hidden');
            } else {
                headerBadge.classList.add('hidden');
            }
            
            // Update list
            if (data.notifications.length > 0) {
                list.innerHTML = data.notifications.map(n => {
                    let icon = '🔔';
                    let bgColor = 'bg-blue-50';
                    
                    if (n.type === 'budget_alert' && n.title.includes('Exceeded')) {
                        icon = '⚠️';
                        bgColor = 'bg-red-50';
                    } else if (n.type === 'budget_alert' && n.title.includes('Warning')) {
                        icon = '⚡';
                        bgColor = 'bg-yellow-50';
                    } else if (n.type === 'goal_achieved') {
                        icon = '🎉';
                        bgColor = 'bg-green-50';
                    } else if (n.type === 'goal_reminder') {
                        icon = '⏰';
                        bgColor = 'bg-blue-50';
                    }
                    
                    return `
                        <div class="px-4 py-3 hover:bg-gray-50 border-b border-gray-100 cursor-pointer ${bgColor}" onclick="markNotificationRead(${n.id})">
                            <div class="flex items-start space-x-3">
                                <div class="text-xl">${n.icon || icon}</div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900 truncate">${n.title}</p>
                                    <p class="text-xs text-gray-600 line-clamp-2">${n.message}</p>
                                    <p class="text-xs text-gray-500 mt-1">${n.created_at}</p>
                                </div>
                            </div>
                        </div>
                    `;
                }).join('');
            } else {
                list.innerHTML = `
                    <div class="px-4 py-8 text-center text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                        </svg>
                        <p class="text-sm">No new notifications</p>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
        }
    }
    
    async function markNotificationRead(id) {
        try {
            await fetch(`/notifications/${id}/read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });
            loadNotificationDropdown();
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }
    
    // Load on page load
    document.addEventListener('DOMContentLoaded', loadNotificationDropdown);
    
    // Refresh every 2 minutes
    setInterval(loadNotificationDropdown, 2 * 60 * 1000);
</script>
