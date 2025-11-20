@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <x-back-button :route="route('budgets.index')" label="Back to Budgets" />
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        <h2 class="text-2xl font-bold mb-6">Create New Budget</h2>
        
        <form method="POST" action="{{ route('budgets.store') }}">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="category_id">
                    Category <span class="text-red-500">*</span>
                </label>
                <select name="category_id" id="category_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('category_id') border-red-500 @enderror" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->icon }} {{ $category->name }}
                        </option>
                    @endforeach
                </select>
                <p class="text-gray-600 text-xs mt-1">Only expense categories can have budgets</p>
                @error('category_id')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="amount">
                    Budget Amount ({{ currency_symbol() }}) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-700 font-semibold">
                        {{ currency_symbol() }}
                    </span>
                    <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount') }}" 
                           class="shadow border rounded w-full py-2 pl-12 pr-3 text-gray-700 @error('amount') border-red-500 @enderror" 
                           placeholder="0.00" required>
                </div>
                @error('amount')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="period">
                    Period <span class="text-red-500">*</span>
                </label>
                <select name="period" id="period" class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('period') border-red-500 @enderror" required>
                    <option value="">Select Period</option>
                    <option value="weekly" {{ old('period') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ old('period') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                    <option value="yearly" {{ old('period') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                </select>
                @error('period')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="start_date">
                    Start Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="start_date" id="start_date" value="{{ old('start_date', date('Y-m-d')) }}" 
                       class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('start_date') border-red-500 @enderror" 
                       required>
                <p class="text-gray-600 text-xs mt-1">The budget will automatically cover the full week, month, or year from this date</p>
                @error('start_date')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('budgets.index') }}" class="text-gray-500 hover:text-gray-700">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Create Budget
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
