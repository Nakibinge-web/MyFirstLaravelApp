<!-- Delete Note Confirmation Modal -->
<div id="delete-note-modal"
     class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm"
     role="dialog"
     aria-modal="true"
     aria-labelledby="delete-note-modal-title">
    <div id="delete-note-modal-content"
         class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all scale-95 opacity-0">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-t-2xl p-6 text-white">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </div>
            </div>
            <h3 id="delete-note-modal-title" class="text-2xl font-bold text-center">Delete Note</h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 text-center text-lg mb-3">
                Are you sure you want to delete this note?
            </p>
            <p id="delete-note-modal-note-title"
               class="text-center font-semibold text-gray-900 bg-gray-100 rounded-lg px-3 py-2 mb-3 break-words"></p>
            <p class="text-gray-500 text-center text-sm">
                This action cannot be undone.
            </p>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 pb-6 flex space-x-3">
            <button type="button"
                    id="delete-note-cancel-btn"
                    onclick="closeDeleteNoteModal()"
                    class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all duration-200">
                Cancel
            </button>
            <form id="delete-note-form" method="POST" action="" class="flex-1">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 text-center">
                    Yes, Delete
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    let deleteNoteCloseTimer = null;

    function openDeleteNoteModal(trigger) {
        const modal = document.getElementById('delete-note-modal');
        const content = document.getElementById('delete-note-modal-content');
        const form = document.getElementById('delete-note-form');
        const noteTitleEl = document.getElementById('delete-note-modal-note-title');
        const cancelBtn = document.getElementById('delete-note-cancel-btn');

        if (!modal || !content || !form || !trigger) {
            return;
        }

        if (deleteNoteCloseTimer) {
            clearTimeout(deleteNoteCloseTimer);
            deleteNoteCloseTimer = null;
        }

        const deleteUrl = trigger.getAttribute('data-delete-url') || '';
        const noteTitle = trigger.getAttribute('data-note-title') || '';

        form.setAttribute('action', deleteUrl);

        if (noteTitleEl) {
            noteTitleEl.textContent = noteTitle;
            noteTitleEl.classList.toggle('hidden', noteTitle.trim() === '');
        }

        modal.classList.remove('hidden');

        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
            if (cancelBtn) {
                cancelBtn.focus();
            }
        }, 10);
    }

    function closeDeleteNoteModal() {
        const modal = document.getElementById('delete-note-modal');
        const content = document.getElementById('delete-note-modal-content');
        const form = document.getElementById('delete-note-form');

        if (!modal || !content || modal.classList.contains('hidden')) {
            return;
        }

        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');

        deleteNoteCloseTimer = setTimeout(() => {
            modal.classList.add('hidden');
            if (form) {
                form.setAttribute('action', '');
            }
            deleteNoteCloseTimer = null;
        }, 200);
    }

    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('delete-note-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeDeleteNoteModal();
                }
            });
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal && !modal.classList.contains('hidden')) {
                closeDeleteNoteModal();
            }
        });
    });
</script>
