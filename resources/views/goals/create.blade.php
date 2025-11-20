@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <x-back-button :route="route('goals.index')" label="Back to Goals" />
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        <h2 class="text-2xl font-bold mb-6">Create New Goal</h2>
        
        <form method="POST" action="{{ route('goals.store') }}">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="name">
                    Goal Name <span class="text-red-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                       class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('name') border-red-500 @enderror" 
                       placeholder="e.g., Emergency Fund, New Car, Vacation" required>
                @error('name')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="target_amount">
                    Target Amount ({{ currency_symbol() }}) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-700 font-semibold">
                        {{ currency_symbol() }}
                    </span>
                    <input type="number" step="0.01" name="target_amount" id="target_amount" value="{{ old('target_amount') }}" 
                           class="shadow border rounded w-full py-2 pl-12 pr-3 text-gray-700 @error('target_amount') border-red-500 @enderror" 
                           placeholder="0.00" required>
                </div>
                @error('target_amount')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="target_date">
                    Target Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="target_date" id="target_date" value="{{ old('target_date') }}" 
                       class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('target_date') border-red-500 @enderror" 
                       min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                @error('target_date')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                    Description
                </label>
                <textarea name="description" id="description" rows="3" 
                          class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('description') border-red-500 @enderror" 
                          placeholder="Optional: Add details about your goal...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('goals.index') }}" class="text-gray-500 hover:text-gray-700">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Create Goal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
