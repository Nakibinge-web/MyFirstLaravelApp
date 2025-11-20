@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Categories</h1>
        <a href="{{ route('categories.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Add Category
        </a>
    </div>

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <!-- Income Categories -->
    <div class="mb-8">
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Income Categories</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($categories->where('type', 'income') as $category)
                <div class="bg-white shadow-md rounded-lg p-4 border-l-4" style="border-color: {{ $category->color }}">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-3">
                            <span class="text-3xl">{{ $category->icon }}</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                                @if($category->is_default)
                                    <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded">Default</span>
                                @endif
                            </div>
                        </div>
                        @if(!$category->is_default)
                            <div class="flex space-x-2">
                                <a href="{{ route('categories.edit', $category) }}" class="text-blue-600 hover:text-blue-900 text-sm">Edit</a>
                                @if($category->transactions()->count() === 0)
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="mt-2 text-sm text-gray-600">
                        {{ $category->transactions()->count() }} transactions
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Expense Categories -->
    <div>
        <h2 class="text-xl font-semibold text-gray-800 mb-4">Expense Categories</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($categories->where('type', 'expense') as $category)
                <div class="bg-white shadow-md rounded-lg p-4 border-l-4" style="border-color: {{ $category->color }}">
                    <div class="flex justify-between items-start">
                        <div class="flex items-center space-x-3">
                            <span class="text-3xl">{{ $category->icon }}</span>
                            <div>
                                <h3 class="font-semibold text-gray-900">{{ $category->name }}</h3>
                                @if($category->is_default)
                                    <span class="text-xs bg-gray-200 text-gray-600 px-2 py-1 rounded">Default</span>
                                @endif
                            </div>
                        </div>
                        @if(!$category->is_default)
                            <div class="flex space-x-2">
                                <a href="{{ route('categories.edit', $category) }}" class="text-blue-600 hover:text-blue-900 text-sm">Edit</a>
                                @if($category->transactions()->count() === 0)
                                    <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this category?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                    </form>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="mt-2 text-sm text-gray-600">
                        {{ $category->transactions()->count() }} transactions
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
