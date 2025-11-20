<!-- Keyboard Shortcuts Modal -->
<div id="shortcuts-modal" class="modal hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-bold">Keyboard Shortcuts</h3>
            <button onclick="document.getElementById('shortcuts-modal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <div class="space-y-3">
            <div class="flex justify-between items-center">
                <span class="text-gray-700">Quick Search</span>
                <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Ctrl + K</kbd>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-700">New Transaction</span>
                <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Ctrl + N</kbd>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-700">Close Modal</span>
                <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Esc</kbd>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-700">Toggle Sidebar</span>
                <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">Ctrl + B</kbd>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-700">Show Shortcuts</span>
                <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 border border-gray-200 rounded">?</kbd>
            </div>
        </div>
        
        <div class="mt-6 text-center">
            <button onclick="document.getElementById('shortcuts-modal').classList.add('hidden')" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Got it!
            </button>
        </div>
    </div>
</div>

<script>
    // Show shortcuts modal with '?' key
    document.addEventListener('keydown', (e) => {
        if (e.key === '?' && !e.target.matches('input, textarea')) {
            e.preventDefault();
            document.getElementById('shortcuts-modal').classList.remove('hidden');
        }
    });
</script>
