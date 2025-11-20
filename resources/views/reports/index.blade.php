@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Financial Reports</h1>
        <div class="flex space-x-2">
            <a href="{{ route('reports.export.pdf', ['year' => $year, 'month' => $month]) }}" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                📄 Export PDF
            </a>
            <a href="{{ route('reports.export.csv', ['year' => $year, 'month' => $month]) }}" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">
                📊 Export CSV
            </a>
            <a href="{{ route('reports.yearly') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                Yearly Report
            </a>
        </div>
    </div>

    <!-- Period Selector -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-6">
        <form method="GET" action="{{ route('reports.index') }}" class="flex items-end space-x-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                <select name="month" class="border rounded px-3 py-2">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                            {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                        </option>
                    @endfor
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Year</label>
                <select name="year" class="border rounded px-3 py-2">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
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
            <p class="text-2xl font-bold text-green-600">${{ number_format($monthlyReport['income'], 2) }}</p>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Total Expenses</p>
            <p class="text-2xl font-bold text-red-600">${{ number_format($monthlyReport['expenses'], 2) }}</p>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Net Savings</p>
            <p class="text-2xl font-bold {{ $monthlyReport['net_savings'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                ${{ number_format($monthlyReport['net_savings'], 2) }}
            </p>
        </div>
        <div class="bg-white shadow-md rounded-lg p-6">
            <p class="text-sm text-gray-600 mb-1">Savings Rate</p>
            <p class="text-2xl font-bold text-blue-600">{{ $monthlyReport['savings_rate'] }}%</p>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Expense Breakdown -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Expense Breakdown</h2>
            @if($expenseBreakdown->count() > 0)
                <div style="height: 300px; position: relative;">
                    <canvas id="expenseChart"></canvas>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No expense data for this period</p>
            @endif
        </div>

        <!-- Income Breakdown -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Income Breakdown</h2>
            @if($incomeBreakdown->count() > 0)
                <div style="height: 300px; position: relative;">
                    <canvas id="incomeChart"></canvas>
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No income data for this period</p>
            @endif
        </div>
    </div>

    <!-- Income vs Expense Trend -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-6">
        <h2 class="text-xl font-semibold mb-4">Income vs Expense Trend (Last 6 Months)</h2>
        <div style="height: 250px; position: relative;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    <!-- Top Expenses & Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Expense Categories -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Top Expense Categories</h2>
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
                <p class="text-gray-500 text-center py-4">No expenses for this period</p>
            @endif
        </div>

        <!-- Transaction Statistics -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Transaction Statistics</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Total Transactions:</span>
                    <span class="font-semibold">{{ $stats['total_transactions'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Income Transactions:</span>
                    <span class="font-semibold text-green-600">{{ $stats['income_transactions'] }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Expense Transactions:</span>
                    <span class="font-semibold text-red-600">{{ $stats['expense_transactions'] }}</span>
                </div>
                <hr>
                <div class="flex justify-between">
                    <span class="text-gray-600">Average Income:</span>
                    <span class="font-semibold">${{ number_format($stats['avg_income'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Average Expense:</span>
                    <span class="font-semibold">${{ number_format($stats['avg_expense'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Largest Income:</span>
                    <span class="font-semibold text-green-600">${{ number_format($stats['largest_income'], 2) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Largest Expense:</span>
                    <span class="font-semibold text-red-600">${{ number_format($stats['largest_expense'], 2) }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Expense Breakdown Chart
    @if($expenseBreakdown->count() > 0)
    new Chart(document.getElementById('expenseChart'), {
        type: 'doughnut',
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
        type: 'doughnut',
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

    // Income vs Expense Trend Chart
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: {!! json_encode(collect($trend)->pluck('month')) !!},
            datasets: [
                {
                    label: 'Income',
                    data: {!! json_encode(collect($trend)->pluck('income')) !!},
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    tension: 0.4
                },
                {
                    label: 'Expenses',
                    data: {!! json_encode(collect($trend)->pluck('expenses')) !!},
                    borderColor: '#EF4444',
                    backgroundColor: 'rgba(239, 68, 68, 0.1)',
                    tension: 0.4
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' }
            },
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
@endsection
