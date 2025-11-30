<!-- Logout Confirmation Modal -->
<div id="logout-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all scale-95 opacity-0" id="logout-modal-content">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-t-2xl p-6 text-white">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-center">Logout Confirmation</h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-600 text-center text-lg mb-2">
                Are you sure you want to logout?
            </p>
            <p class="text-gray-500 text-center text-sm">
                You'll need to sign in again to access your account.
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 pb-6 flex space-x-3">
            <button onclick="closeLogoutModal()" 
                    class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all duration-200 transform hover:scale-105">
                <span class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </span>
            </button>
            <a href="{{ route('logout.get') }}" 
               class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105 text-center">
                <span class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Yes, Logout
                </span>
            </a>
        </div>
    </div>
</div>

<script>
    function openLogoutModal() {
        const modal = document.getElementById('logout-modal');
        const content = document.getElementById('logout-modal-content');
        
        modal.classList.remove('hidden');
        
        // Trigger animation
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
    }

    function closeLogoutModal() {
        const modal = document.getElementById('logout-modal');
        const content = document.getElementById('logout-modal-content');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 200);
    }

    // Close modal when clicking outside
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('logout-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeLogoutModal();
                }
            });
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeLogoutModal();
            }
        });
    });
</script>
