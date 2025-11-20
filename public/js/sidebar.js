// Sidebar specific JavaScript enhancements

// Auto-close sidebar on mobile when clicking a link
document.addEventListener('DOMContentLoaded', () => {
    const sidebar = document.getElementById('sidebar');
    const sidebarLinks = sidebar?.querySelectorAll('a');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    // Close sidebar when clicking a link on mobile
    sidebarLinks?.forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth < 1024) {
                sidebar.classList.add('-translate-x-full');
                sidebarOverlay.classList.add('hidden');
            }
        });
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window.innerWidth >= 1024) {
                // Desktop: ensure sidebar is visible
                sidebar?.classList.remove('-translate-x-full');
                sidebarOverlay?.classList.add('hidden');
            } else {
                // Mobile: ensure sidebar is hidden
                sidebar?.classList.add('-translate-x-full');
                sidebarOverlay?.classList.add('hidden');
            }
        }, 250);
    });
    
    // Keyboard shortcut to toggle sidebar (Ctrl+B)
    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            const isHidden = sidebar?.classList.contains('-translate-x-full');
            
            if (isHidden) {
                sidebar?.classList.remove('-translate-x-full');
                sidebarOverlay?.classList.remove('hidden');
            } else {
                sidebar?.classList.add('-translate-x-full');
                sidebarOverlay?.classList.add('hidden');
            }
        }
    });
});

// Highlight active navigation item based on current URL
function highlightActiveNav() {
    const currentPath = window.location.pathname;
    const navLinks = document.querySelectorAll('#sidebar nav a');
    
    navLinks.forEach(link => {
        const linkPath = new URL(link.href).pathname;
        
        if (currentPath === linkPath || currentPath.startsWith(linkPath + '/')) {
            link.classList.add('bg-blue-600', 'hover:bg-blue-700');
            link.classList.remove('hover:bg-gray-800');
        }
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', highlightActiveNav);
