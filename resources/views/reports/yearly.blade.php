@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Yearly Report - {{ $year }}</h1>
        <a href="{{ route('reports.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            ← Monthly Reports
        </a>
    </div>

    <!-- Year Selector -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('reports.yearly') }}" class="flex items-end space-x-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select name="year" class="border rounded px-3 py-2">
                    @for($y = date('Y'); $y >= date('Y') - 10; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>
            <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                View Report
            </button>
        </form>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Total Income</p>
            <p class="text-2xl font-bold text-green-600">${{ number_format($yearlyReport['income'], 2) }}</p>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Total Expenses</p>
            <p class="text-2xl font-bold text-red-600">${{ number_format($yearlyReport['expenses'], 2) }}</p>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Net Savings</p>
            <p class="text-2xl font-bold {{ $yearlyReport['net_savings'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                ${{ number_format($yearlyReport['net_savings'], 2) }}
            </p>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Savings Rate</p>
            <p class="text-2xl font-bold text-blue-600">{{ $yearlyReport['savings_rate'] }}%</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Expense Breakdown -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Annual Expense Breakdown</h2>
            @if($expenseBreakdown->count() > 0)
                <div style="height: 300px; position: relative;">
                    <canvas id="expenseChart"></canvas>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No expense data for this year</p>
            @endif
        </div>

        <!-- Income Breakdown -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Annual Income Breakdown</h2>
            @if($incomeBreakdown->count() > 0)
                <div style="height: 300px; position: relative;">
                    <canvas id="incomeChart"></canvas>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No income data for this year</p>
            @endif
        </div>
    </div>

    <!-- Top Expenses -->
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-xl font-semibold mb-4">Top 10 Expense Categories</h2>
        @if($topExpenses->count() > 0)
            <div class="space-y-3">
                @foreach($topExpenses as $expense)
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl">{{ $expense['icon'] }}</span>
                            <span class="font-medium">{{ $expense['category'] }}</span>
                            <span class="text-sm text-gray-500">({{ $expense['count'] }} transactions)</span>
                        </div>
                        <span class="font-bold text-red-600">${{ number_format($expense['amount'], 2) }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-gray-500 text-center py-4">No expenses for this year</p>
        @endif
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Expense Breakdown Chart
    @if($expenseBreakdown->count() > 0)
    new Chart(document.getElementById('expenseChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($expenseBreakdown->pluck('category')) !!},
            datasets: [{
                data: {!! json_encode($expenseBreakdown->pluck('amount')) !!},
                backgroundColor: {!! json_encode($expenseBreakdown->pluck('color')) !!},
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 10,
                        font: { size: 11 }
                    }
                }
            }
        }
    });
    @endif

    // Income Breakdown Chart
    @if($incomeBreakdown->count() > 0)
    new Chart(document.getElementById('incomeChart'), {
        type: 'pie',
        data: {
            labels: {!! json_encode($incomeBreakdown->pluck('category')) !!},
            datasets: [{
                data: {!! json_encode($incomeBreakdown->pluck('amount')) !!},
                backgroundColor: {!! json_encode($incomeBreakdown->pluck('color')) !!},
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { 
                    position: 'right',
                    labels: {
                        boxWidth: 15,
                        padding: 10,
                        font: { size: 11 }
                    }
                }
            }
        }
    });
    @endif
</script>
@endsection
