// Toast Notification System
class ToastNotification {
    constructor() {
        this.container = this.createContainer();
        this.lastNotificationCount = 0;
    }

    createContainer() {
        const container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'fixed top-20 right-4 z-50 space-y-3';
        container.style.maxWidth = '400px';
        document.body.appendChild(container);
        return container;
    }

    show(notification) {
        const toast = document.createElement('div');
        toast.className = 'toast-notification bg-white shadow-lg rounded-lg p-4 border-l-4 transform transition-all duration-300 translate-x-full opacity-0';
        
        // Set border color based on type
        let borderColor = 'border-blue-500';
        let icon = '🔔';
        
        if (notification.type === 'budget_alert' && notification.title.includes('Exceeded')) {
            borderColor = 'border-red-500';
            icon = '⚠️';
        } else if (notification.type === 'budget_alert' && notification.title.includes('Warning')) {
            borderColor = 'border-yellow-500';
            icon = '⚡';
        } else if (notification.type === 'goal_achieved') {
            borderColor = 'border-green-500';
            icon = '🎉';
        } else if (notification.type === 'goal_reminder') {
            borderColor = 'border-blue-500';
            icon = '⏰';
        }
        
        toast.classList.add(borderColor);
        
        toast.innerHTML = `
            <div class="flex items-start space-x-3">
                <div class="text-2xl">${notification.icon || icon}</div>
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900">${notification.title}</h4>
                    <p class="text-sm text-gray-600 mt-1">${notification.message}</p>
                    <p class="text-xs text-gray-500 mt-1">${notification.created_at}</p>
                </div>
                <button class="text-gray-400 hover:text-gray-600" onclick="this.closest('.toast-notification').remove()">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        `;
        
        this.container.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.classList.remove('translate-x-full', 'opacity-0');
        }, 10);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('translate-x-full', 'opacity-0');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
    }

    async checkForNew() {
        try {
            const response = await fetch('/notifications/unread');
            const data = await response.json();
            
            // If there are new notifications since last check
            if (data.count > this.lastNotificationCount && this.lastNotificationCount > 0) {
                // Show only the new notifications
                const newCount = data.count - this.lastNotificationCount;
                const newNotifications = data.notifications.slice(0, newCount);
                
                newNotifications.forEach((notification, index) => {
                    setTimeout(() => {
                        this.show(notification);
                    }, index * 300); // Stagger the notifications
                });
            }
            
            this.lastNotificationCount = data.count;
            
            // Update badge
            const badge = document.getElementById('sidebarNotificationBadge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count > 9 ? '9+' : data.count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
            }
        } catch (error) {
            console.error('Error checking notifications:', error);
        }
    }

    startPolling(interval = 60000) { // Default: check every minute
        // Initial check
        this.checkForNew();
        
        // Poll for new notifications
        setInterval(() => {
            this.checkForNew();
        }, interval);
    }
}

// Initialize toast notifications when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (document.body.classList.contains('authenticated') || document.querySelector('#sidebar')) {
        const toastNotification = new ToastNotification();
        toastNotification.startPolling(60000); // Check every minute
    }
});

// Manual notification trigger function (can be called from anywhere)
function showNotification(title, message, type = 'info', icon = null) {
    const toast = new ToastNotification();
    toast.show({
        title: title,
        message: message,
        type: type,
        icon: icon,
        created_at: 'Just now'
    });
}
