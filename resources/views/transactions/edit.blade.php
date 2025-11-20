@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <x-back-button :route="route('transactions.index')" label="Back to Transactions" />
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        <h2 class="text-2xl font-bold mb-6">Edit Transaction</h2>
        
        <form method="POST" action="{{ route('transactions.update', $transaction) }}">
            @csrf
            @method('PUT')
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="type">
                    Type <span class="text-red-500">*</span>
                </label>
                <select name="type" id="type" class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('type') border-red-500 @enderror" required>
                    <option value="">Select Type</option>
                    <option value="income" {{ old('type', $transaction->type) == 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ old('type', $transaction->type) == 'expense' ? 'selected' : '' }}>Expense</option>
                </select>
                @error('type')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="category_id">
                    Category <span class="text-red-500">*</span>
                </label>
                <select name="category_id" id="category_id" class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('category_id') border-red-500 @enderror" required>
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id) == $category->id ? 'selected' : '' }}>
                            {{ $category->icon }} {{ $category->name }} ({{ ucfirst($category->type) }})
                        </option>
                    @endforeach
                </select>
                @error('category_id')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="amount">
                    Amount ({{ currency_symbol() }}) <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-700 font-semibold">
                        {{ currency_symbol() }}
                    </span>
                    <input type="number" step="0.01" name="amount" id="amount" value="{{ old('amount', $transaction->amount) }}" 
                           class="shadow border rounded w-full py-2 pl-12 pr-3 text-gray-700 @error('amount') border-red-500 @enderror" 
                           placeholder="0.00" required>
                </div>
                @error('amount')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="date">
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date" name="date" id="date" value="{{ old('date', $transaction->date->format('Y-m-d')) }}" 
                       class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('date') border-red-500 @enderror" 
                       max="{{ date('Y-m-d') }}" required>
                @error('date')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="description">
                    Description <span class="text-red-500">*</span>
                </label>
                <textarea name="description" id="description" rows="3" 
                          class="shadow border rounded w-full py-2 px-3 text-gray-700 @error('description') border-red-500 @enderror" 
                          placeholder="Enter transaction details..." required>{{ old('description', $transaction->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('transactions.index') }}" class="text-gray-500 hover:text-gray-700">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Update Transaction
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
