@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <h1 class="text-3xl font-bold text-gray-900 mb-6">Profile Settings</h1>
    
    <div class="grid grid-cols-1 gap-6">
        <!-- Update Profile Information -->
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
            <h2 class="text-xl font-bold mb-4">Profile Information</h2>
            
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                        Name
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('name') border-red-500 @enderror" 
                           id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                        Email
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('email') border-red-500 @enderror" 
                           id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-end">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Update Profile
                    </button>
                </div>
            </form>
        </div>

        <!-- Update Password -->
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
            <h2 class="text-xl font-bold mb-4">Update Password</h2>
            
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="current_password">
                        Current Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('current_password') border-red-500 @enderror" 
                           id="current_password" type="password" name="current_password" required>
                    @error('current_password')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                        New Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline @error('password') border-red-500 @enderror" 
                           id="password" type="password" name="password" required>
                    @error('password')
                        <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="password_confirmation">
                        Confirm New Password
                    </label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="password_confirmation" type="password" name="password_confirmation" required>
                </div>

                <div class="flex items-center justify-end">
                    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" type="submit">
                        Update Password
                    </button>
                </div>
            </form>
        </div>

        <!-- Delete Account -->
        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 border-2 border-red-200">
            <h2 class="text-xl font-bold mb-4 text-red-600">Delete Account</h2>
            
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                <div class="flex items-start">
                    <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-red-800 font-semibold mb-1">Warning: This action is irreversible!</h3>
                        <p class="text-red-700 text-sm">
                            Deleting your account will permanently remove all your data including:
                        </p>
                        <ul class="text-red-700 text-sm mt-2 ml-4 list-disc">
                            <li>All transactions and financial records</li>
                            <li>Budget plans and tracking data</li>
                            <li>Financial goals and progress</li>
                            <li>Categories and preferences</li>
                            <li>Notifications and settings</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-gray-600 text-sm">
                    Once deleted, your account cannot be recovered.
                </p>
                <button onclick="openDeleteAccountModal()" 
                        class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-2 px-6 rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <span class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                        Delete Account
                    </span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Confirmation Modal -->
<div id="delete-account-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full transform transition-all scale-95 opacity-0" id="delete-account-modal-content">
        <!-- Modal Header -->
        <div class="bg-gradient-to-r from-red-600 to-red-700 rounded-t-2xl p-6 text-white">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-white bg-opacity-20 rounded-full p-4">
                    <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold text-center">Delete Account?</h3>
        </div>

        <!-- Modal Body -->
        <div class="p-6">
            <p class="text-gray-700 text-center text-lg mb-4 font-semibold">
                Are you absolutely sure?
            </p>
            <p class="text-gray-600 text-center text-sm mb-4">
                This will permanently delete your account and all associated data. This action cannot be undone.
            </p>
            
            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <p class="text-yellow-800 text-sm font-medium">
                    ⚠️ All your financial data will be lost forever!
                </p>
            </div>

            <form id="delete-account-form" method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2">
                        Type "DELETE" to confirm:
                    </label>
                    <input type="text" 
                           id="delete-confirmation" 
                           class="shadow border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline focus:border-red-500" 
                           placeholder="Type DELETE in capital letters"
                           autocomplete="off">
                    <p class="text-xs text-gray-500 mt-1">This helps prevent accidental deletions</p>
                </div>
            </form>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 pb-6 flex space-x-3">
            <button onclick="closeDeleteAccountModal()" 
                    class="flex-1 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold rounded-lg transition-all duration-200 transform hover:scale-105">
                <span class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                    Cancel
                </span>
            </button>
            <button onclick="confirmDeleteAccount()" 
                    id="confirm-delete-btn"
                    disabled
                    class="flex-1 px-6 py-3 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-semibold rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none">
                <span class="flex items-center justify-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Yes, Delete Forever
                </span>
            </button>
        </div>
    </div>
</div>

<script>
    function openDeleteAccountModal() {
        const modal = document.getElementById('delete-account-modal');
        const content = document.getElementById('delete-account-modal-content');
        
        modal.classList.remove('hidden');
        
        // Trigger animation
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
        
        // Focus on input
        setTimeout(() => {
            document.getElementById('delete-confirmation').focus();
        }, 300);
    }

    function closeDeleteAccountModal() {
        const modal = document.getElementById('delete-account-modal');
        const content = document.getElementById('delete-account-modal-content');
        const input = document.getElementById('delete-confirmation');
        const btn = document.getElementById('confirm-delete-btn');
        
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            input.value = '';
            btn.disabled = true;
        }, 200);
    }

    function confirmDeleteAccount() {
        const input = document.getElementById('delete-confirmation');
        if (input.value === 'DELETE') {
            document.getElementById('delete-account-form').submit();
        }
    }

    // Enable/disable delete button based on confirmation text
    document.addEventListener('DOMContentLoaded', function() {
        const input = document.getElementById('delete-confirmation');
        const btn = document.getElementById('confirm-delete-btn');
        
        if (input && btn) {
            input.addEventListener('input', function() {
                if (this.value === 'DELETE') {
                    btn.disabled = false;
                    btn.classList.add('animate-pulse');
                } else {
                    btn.disabled = true;
                    btn.classList.remove('animate-pulse');
                }
            });

            // Allow Enter key to submit when enabled
            input.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && this.value === 'DELETE') {
                    e.preventDefault();
                    confirmDeleteAccount();
                }
            });
        }

        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeDeleteAccountModal();
            }
        });

        // Close modal when clicking outside
        const modal = document.getElementById('delete-account-modal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeDeleteAccountModal();
                }
            });
        }
    });
</script>
@endsection
