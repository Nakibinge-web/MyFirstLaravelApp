@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Financial Goals</h1>
        <a href="{{ route('goals.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Add Goal
        </a>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('goals.index', ['status' => 'active']) }}" 
               class="border-b-2 py-4 px-1 text-sm font-medium {{ $status === 'active' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Active
            </a>
            <a href="{{ route('goals.index', ['status' => 'completed']) }}" 
               class="border-b-2 py-4 px-1 text-sm font-medium {{ $status === 'completed' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Completed
            </a>
            <a href="{{ route('goals.index', ['status' => 'paused']) }}" 
               class="border-b-2 py-4 px-1 text-sm font-medium {{ $status === 'paused' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Paused
            </a>
            <a href="{{ route('goals.index', ['status' => 'all']) }}" 
               class="border-b-2 py-4 px-1 text-sm font-medium {{ $status === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                All
            </a>
        </nav>
    </div>

    @if($goalsWithProgress->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($goalsWithProgress as $goal)
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-xl font-semibold text-gray-900">{{ $goal->name }}</h3>
                                <span class="inline-block px-2 py-1 text-xs rounded mt-1
                                    @if($goal->status === 'completed') bg-green-100 text-green-800
                                    @elseif($goal->status === 'paused') bg-gray-100 text-gray-800
                                    @else bg-blue-100 text-blue-800
                                    @endif">
                                    {{ ucfirst($goal->status) }}
                                </span>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('goals.show', $goal) }}" class="text-blue-600 hover:text-blue-900 text-sm">View</a>
                                <a href="{{ route('goals.edit', $goal) }}" class="text-blue-600 hover:text-blue-900 text-sm">Edit</a>
                                <form action="{{ route('goals.destroy', $goal) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                </form>
                            </div>
                        </div>

                        @if($goal->description)
                            <p class="text-gray-600 text-sm mb-4">{{ $goal->description }}</p>
                        @endif

                        <!-- Progress -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Progress</span>
                                <span class="font-semibold">{{ currency_format($goal->current_amount) }} / {{ currency_format($goal->target_amount) }}</span>
                            </div>
                            
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-500 h-3 rounded-full transition-all duration-300"
                                    style="width: {{ min($goal->progress['percentage'], 100) }}%">
                                </div>
                            </div>
                            
                            <div class="flex justify-between text-xs mt-1">
                                <span class="text-blue-600 font-semibold">{{ $goal->progress['percentage'] }}%</span>
                                <span class="text-gray-600">{{ currency_format($goal->progress['remaining']) }} remaining</span>
                            </div>
                        </div>

                        <!-- Target Date -->
                        <div class="flex justify-between items-center text-sm">
                            <div>
                                <span class="text-gray-600">Target Date:</span>
                                <span class="font-semibold {{ $goal->progress['is_overdue'] ? 'text-red-600' : 'text-gray-900' }}">
                                    {{ $goal->target_date->format('M d, Y') }}
                                </span>
                            </div>
                            <div class="text-right">
                                @if($goal->progress['is_completed'])
                                    <span class="text-green-600 font-semibold">✓ Completed!</span>
                                @elseif($goal->progress['is_overdue'])
                                    <span class="text-red-600 font-semibold">Overdue</span>
                                @else
                                    <span class="text-gray-600">{{ $goal->progress['days_remaining'] }} days left</span>
                                @endif
                            </div>
                        </div>

                        @if($goal->estimated_completion && !$goal->progress['is_completed'])
                            <div class="mt-2 text-xs text-gray-500">
                                Estimated completion: {{ $goal->estimated_completion->format('M d, Y') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg px-6 py-12 text-center">
            <p class="text-lg text-gray-500 mb-4">No {{ $status !== 'all' ? $status : '' }} goals found.</p>
            <a href="{{ route('goals.create') }}" class="text-blue-500 hover:text-blue-700">
                Create your first financial goal
            </a>
        </div>
    @endif
</div>
@endsection
