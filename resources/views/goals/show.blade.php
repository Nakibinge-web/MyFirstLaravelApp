@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <x-back-button :route="route('goals.index')" label="Back to Goals" />
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">{{ $goal->name }}</h1>
                    <span class="inline-block px-3 py-1 text-sm rounded mt-2
                        @if($goal->status === 'completed') bg-green-100 text-green-800
                        @elseif($goal->status === 'paused') bg-gray-100 text-gray-800
                        @else bg-blue-100 text-blue-800
                        @endif">
                        {{ ucfirst($goal->status) }}
                    </span>
                </div>
                <div class="flex space-x-2">
                    @if($goal->status !== 'completed')
                        <form action="{{ route('goals.toggle', $goal) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
                                {{ $goal->status === 'active' ? 'Pause' : 'Resume' }}
                            </button>
                        </form>
                    @endif
                    <a href="{{ route('goals.edit', $goal) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Edit
                    </a>
                </div>
            </div>

            @if($goal->description)
                <p class="text-gray-600 mb-6">{{ $goal->description }}</p>
            @endif

            <!-- Progress Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Progress</h2>
                
                <div class="mb-4">
                    <div class="flex justify-between text-lg mb-2">
                        <span class="text-gray-600">Current</span>
                        <span class="font-bold text-2xl">${{ number_format($goal->current_amount, 2) }}</span>
                    </div>
                    <div class="flex justify-between text-lg mb-2">
                        <span class="text-gray-600">Target</span>
                        <span class="font-bold text-2xl">${{ number_format($goal->target_amount, 2) }}</span>
                    </div>
                    
                    <div class="w-full bg-gray-200 rounded-full h-6 mt-4">
                        <div class="bg-blue-500 h-6 rounded-full transition-all duration-300 flex items-center justify-center text-white text-sm font-semibold"
                            style="width: {{ min($progress['percentage'], 100) }}%">
                            {{ $progress['percentage'] }}%
                        </div>
                    </div>
                    
                    <div class="flex justify-between text-sm mt-2">
                        <span class="text-gray-600">${{ number_format($progress['remaining'], 2) }} remaining</span>
                        @if($progress['is_completed'])
                            <span class="text-green-600 font-semibold">✓ Goal Achieved!</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Timeline Section -->
            <div class="mb-8">
                <h2 class="text-xl font-semibold mb-4">Timeline</h2>
                
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 p-4 rounded">
                        <p class="text-sm text-gray-600">Target Date</p>
                        <p class="text-lg font-semibold {{ $progress['is_overdue'] ? 'text-red-600' : 'text-gray-900' }}">
                            {{ $goal->target_date->format('M d, Y') }}
                        </p>
                    </div>
                    
                    <div class="bg-gray-50 p-4 rounded">
                        <p class="text-sm text-gray-600">Days Remaining</p>
                        <p class="text-lg font-semibold {{ $progress['is_overdue'] ? 'text-red-600' : 'text-gray-900' }}">
                            @if($progress['is_completed'])
                                Completed
                            @elseif($progress['is_overdue'])
                                Overdue
                            @else
                                {{ $progress['days_remaining'] }} days
                            @endif
                        </p>
                    </div>
                </div>

                @if($estimatedCompletion && !$progress['is_completed'])
                    <div class="mt-4 bg-blue-50 p-4 rounded">
                        <p class="text-sm text-gray-600">Estimated Completion</p>
                        <p class="text-lg font-semibold text-blue-600">{{ $estimatedCompletion->format('M d, Y') }}</p>
                        <p class="text-xs text-gray-500 mt-1">Based on your current progress rate</p>
                    </div>
                @endif
            </div>

            <!-- Update Progress Form -->
            @if($goal->status === 'active' && !$progress['is_completed'])
                <div class="border-t pt-6">
                    <h2 class="text-xl font-semibold mb-4">Update Progress</h2>
                    
                    <form action="{{ route('goals.progress', $goal) }}" method="POST" class="flex items-end space-x-4">
                        @csrf
                        
                        <div class="flex-1">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="amount">
                                Add Amount
                            </label>
                            <input type="number" step="0.01" name="amount" id="amount" 
                                   class="shadow border rounded w-full py-2 px-3 text-gray-700" 
                                   placeholder="0.00" required>
                        </div>
                        
                        <button type="submit" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-6 rounded">
                            Add Progress
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
