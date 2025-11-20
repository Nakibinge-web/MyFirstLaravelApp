@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <x-back-button :route="route('categories.index')" label="Back to Categories" />
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        <h2 class="text-2xl font-bold mb-6">Add New Category</h2>
        
        <form method="POST" action="{{ route('categories.store') }}">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Category Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                       class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('name') border-red-500 @enderror" 
                       placeholder="e.g., Groceries" required>
                @error('name')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="type">
                    Type <span class="text-red-500">*</span>
                </label>
                <select name="type" id="type" class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('type') border-red-500 @enderror" required>
                    <option value="">Select Type</option>
                    <option value="income" {{ old('type') == 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ old('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="icon">
                    Icon (Emoji)
                </label>
                <input type="text" name="icon" id="icon" value="{{ old('icon', '📁') }}" 
                       class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('icon') border-red-500 @enderror" 
                       placeholder="📁" maxlength="10">
                <p class="text-gray-600 text-xs mt-1">Use an emoji to represent this category</p>
                @error('icon')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="color">
                    Color
                </label>
                <div class="flex items-center space-x-2">
                    <input type="color" name="color" id="color" value="{{ old('color', '#4F46E5') }}" 
                           class="h-10 w-20 border rounded cursor-pointer">
                    <input type="text" id="color-text" value="{{ old('color', '#4F46E5') }}" 
                           class="shadow border rounded py-2 px-3 text-gray-700 flex-1" 
                           placeholder="#4F46E5" readonly>
                </div>
                <p class="text-gray-600 text-xs mt-1">Choose a color to identify this category</p>
                @error('color')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('categories.index') }}" class="text-gray-500 hover:text-gray-700">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Create Category
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Sync color picker with text input
    const colorPicker = document.getElementById('color');
    const colorText = document.getElementById('color-text');
    
    colorPicker.addEventListener('input', function() {
        colorText.value = this.value.toUpperCase();
    });
</script>
@endsection
