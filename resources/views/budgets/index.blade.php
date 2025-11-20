@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Budgets</h1>
        <a href="{{ route('budgets.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Add Budget
        </a>
    </div>

    @if($budgetsWithUtilization->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($budgetsWithUtilization as $budget)
                <div class="bg-white shadow-md rounded-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div class="flex items-center space-x-3">
                                <span class="text-3xl">{{ $budget->category->icon }}</span>
                                <div>
                                    <h3 class="font-semibold text-gray-900">{{ $budget->category->name }}</h3>
                                    <p class="text-sm text-gray-600">{{ ucfirst($budget->period) }}</p>
                                </div>
                            </div>
                            <div class="flex space-x-2">
                                <a href="{{ route('budgets.edit', $budget) }}" class="text-blue-600 hover:text-blue-900 text-sm">Edit</a>
                                <form action="{{ route('budgets.destroy', $budget) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this budget?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900 text-sm">Delete</button>
                                </form>
                            </div>
                        </div>

                        <!-- Budget Amount -->
                        <div class="mb-4">
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-gray-600">Spent</span>
                                <span class="font-semibold">{{ currency_format($budget->utilization['spent']) }} / {{ currency_format($budget->amount) }}</span>
                            </div>
                            
                            <!-- Progress Bar -->
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full transition-all duration-300 
                                    @if($budget->utilization['status'] === 'exceeded') bg-red-500
                                    @elseif($budget->utilization['status'] === 'warning') bg-yellow-500
                                    @else bg-green-500
                                    @endif"
                                    style="width: {{ min($budget->utilization['percentage'], 100) }}%">
                                </div>
                            </div>
                            
                            <div class="flex justify-between text-xs mt-1">
                                <span class="
                                    @if($budget->utilization['status'] === 'exceeded') text-red-600
                                    @elseif($budget->utilization['status'] === 'warning') text-yellow-600
                                    @else text-green-600
                                    @endif font-semibold">
                                    {{ $budget->utilization['percentage'] }}%
                                </span>
                                <span class="text-gray-600">
                                    {{ currency_format(abs($budget->utilization['remaining'])) }} 
                                    {{ $budget->utilization['remaining'] >= 0 ? 'remaining' : 'over' }}
                                </span>
                            </div>
                        </div>

                        <!-- Status Badge -->
                        @if($budget->utilization['status'] === 'exceeded')
                            <div class="bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded text-sm">
                                ⚠️ Budget Exceeded!
                            </div>
                        @elseif($budget->utilization['status'] === 'warning')
                            <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-3 py-2 rounded text-sm">
                                ⚠️ Approaching Limit
                            </div>
                        @else
                            <div class="bg-green-100 border border-green-400 text-green-700 px-3 py-2 rounded text-sm">
                                ✓ On Track
                            </div>
                        @endif

                        <!-- Date Range -->
                        <div class="mt-4 text-xs text-gray-500">
                            {{ $budget->start_date->format('M d, Y') }} - {{ $budget->end_date->format('M d, Y') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="bg-white shadow-md rounded-lg px-6 py-12 text-center">
            <p class="text-lg text-gray-500 mb-4">No budgets created yet.</p>
            <a href="{{ route('budgets.create') }}" class="text-blue-500 hover:text-blue-700">
                Create your first budget to track your spending
            </a>
        </div>
    @endif
</div>
@endsection
