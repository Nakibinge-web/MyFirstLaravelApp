@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6 flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ auth()->user()->name }}! 👋</h1>
            <p class="text-gray-600 mt-1">Here's your financial overview for {{ date('F Y') }}</p>
        </div>
        <div class="flex items-center space-x-3">
            <x-currency-selector />
            <span id="lastUpdated" class="text-sm text-gray-500">Last updated: just now</span>
            <button id="refreshBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded flex items-center space-x-2">
                <svg id="refreshIcon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                <span>Refresh</span>
            </button>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <svg class="animate-spin h-6 w-6 text-blue-500" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-gray-700 font-medium">Refreshing dashboard...</span>
        </div>
    </div>

    <!-- Monthly Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-gradient-to-br from-green-500 to-green-600 text-white shadow-lg rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Monthly Income</p>
                    <p class="text-3xl font-bold mt-1" data-stat="income">{{ currency_format($monthlyStats['income'], null, 0) }}</p>
                </div>
                <div class="text-5xl opacity-20">💰</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-600 text-white shadow-lg rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Monthly Expenses</p>
                    <p class="text-3xl font-bold mt-1" data-stat="expenses">{{ currency_format($monthlyStats['expenses'], null, 0) }}</p>
                </div>
                <div class="text-5xl opacity-20">💸</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Net Savings</p>
                    <p class="text-3xl font-bold mt-1" data-stat="savings">{{ currency_format($monthlyStats['net_savings'], null, 0) }}</p>
                </div>
                <div class="text-5xl opacity-20">💵</div>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 text-white shadow-lg rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Savings Rate</p>
                    <p class="text-3xl font-bold mt-1" data-stat="rate">{{ $monthlyStats['savings_rate'] }}%</p>
                </div>
                <div class="text-5xl opacity-20">📊</div>
            </div>
        </div>
    </div>

    <!-- Net Worth & Quick Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Net Worth</h3>
            <p class="text-4xl font-bold {{ $netWorth >= 0 ? 'text-green-600' : 'text-red-600' }}" data-stat="networth">
                {{ currency_format($netWorth) }}
            </p>
            <p class="text-sm text-gray-500 mt-2">Total income - Total expenses</p>
        </div>

        <div class="bg-white shadow-md rounded-lg p-6 col-span-2">
            <h3 class="text-lg font-semibold mb-4">Quick Stats</h3>
            <div class="grid grid-cols-2 gap-4">
                <div class="flex items-center space-x-3">
                    <div class="bg-blue-100 p-3 rounded-lg">
                        <span class="text-2xl">📝</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" data-stat="transactions">{{ $quickStats['total_transactions'] }}</p>
                        <p class="text-sm text-gray-600">Transactions</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-green-100 p-3 rounded-lg">
                        <span class="text-2xl">💼</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" data-stat="budgets">{{ $quickStats['total_budgets'] }}</p>
                        <p class="text-sm text-gray-600">Budgets</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-purple-100 p-3 rounded-lg">
                        <span class="text-2xl">🎯</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" data-stat="active-goals">{{ $quickStats['active_goals'] }}</p>
                        <p class="text-sm text-gray-600">Active Goals</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="bg-yellow-100 p-3 rounded-lg">
                        <span class="text-2xl">⭐</span>
                    </div>
                    <div>
                        <p class="text-2xl font-bold" data-stat="total-goals">{{ $quickStats['total_goals'] }}</p>
                        <p class="text-sm text-gray-600">Total Goals</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- Recent Transactions -->
        <div class="lg:col-span-2 bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Recent Transactions</h2>
                <a href="{{ route('transactions.index') }}" class="group inline-flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <span>View All</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>
            @if($recentTransactions->count() > 0)
                <div class="space-y-3" id="recentTransactions">
                    @foreach($recentTransactions as $transaction)
                        <div class="flex justify-between items-center py-2 border-b last:border-0">
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl">{{ $transaction->category->icon }}</span>
                                <div>
                                    <p class="font-medium">{{ $transaction->category->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $transaction->date->format('M d, Y') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold {{ $transaction->type == 'income' ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction->type == 'income' ? '+' : '-' }}{{ currency_format($transaction->amount) }}
                                </p>
                                <p class="text-xs text-gray-500">{{ Str::limit($transaction->description, 20) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No transactions yet. <a href="{{ route('transactions.create') }}" class="text-blue-500">Add one</a></p>
            @endif
        </div>

        <!-- Spending Trend -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">7-Day Spending</h2>
            <div style="height: 200px; position: relative;">
                <canvas id="spendingChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Budgets & Goals -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Active Budgets -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Budget Status</h2>
                <a href="{{ route('budgets.index') }}" class="group inline-flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <span>View All</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>
            @if($budgetsWithUtilization->count() > 0)
                <div class="space-y-4" id="budgetsContainer">
                    @foreach($budgetsWithUtilization->take(3) as $budget)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium">{{ $budget->category->icon }} {{ $budget->category->name }}</span>
                                <span class="text-gray-600">{{ currency_format($budget->utilization['spent'], null, 0) }} / {{ currency_format($budget->amount, null, 0) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all
                                    @if($budget->utilization['status'] === 'exceeded') bg-red-500
                                    @elseif($budget->utilization['status'] === 'warning') bg-yellow-500
                                    @else bg-green-500
                                    @endif"
                                    style="width: {{ min($budget->utilization['percentage'], 100) }}%">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $budget->utilization['percentage'] }}% used</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No active budgets. <a href="{{ route('budgets.create') }}" class="text-blue-500">Create one</a></p>
            @endif
        </div>

        <!-- Active Goals -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-semibold">Active Goals</h2>
                <a href="{{ route('goals.index') }}" class="group inline-flex items-center space-x-2 px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white text-sm font-medium rounded-lg shadow-md hover:shadow-lg transition-all duration-200 transform hover:scale-105">
                    <span>View All</span>
                    <svg class="w-4 h-4 transform group-hover:translate-x-1 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </a>
            </div>
            @if($goalsWithProgress->count() > 0)
                <div class="space-y-4" id="goalsContainer">
                    @foreach($goalsWithProgress->take(3) as $goal)
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium">{{ $goal->name }}</span>
                                <span class="text-gray-600">{{ currency_format($goal->current_amount, null, 0) }} / {{ currency_format($goal->target_amount, null, 0) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full transition-all"
                                    style="width: {{ min($goal->progress['percentage'], 100) }}%">
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">{{ $goal->progress['percentage'] }}% • {{ $goal->progress['days_remaining'] }} days left</p>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 text-center py-8">No active goals. <a href="{{ route('goals.create') }}" class="text-blue-500">Create one</a></p>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    let spendingChart;
    const currencySymbol = '{{ currency_symbol() }}';
    const currencyCode = '{{ auth()->user()->currency ?? 'USD' }}';
    
    // Currencies that have symbol after amount
    const symbolAfterCurrencies = ['EUR', 'SEK', 'NOK', 'DKK', 'CZK', 'HUF', 'PLN'];
    const symbolAfter = symbolAfterCurrencies.includes(currencyCode);

    // Initialize Spending Trend Chart
    function initSpendingChart(data) {
        const ctx = document.getElementById('spendingChart');
        if (spendingChart) {
            spendingChart.destroy();
        }
        spendingChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: 'Daily Spending',
                    data: data.map(d => d.amount),
                    backgroundColor: 'rgba(239, 68, 68, 0.5)',
                    borderColor: '#EF4444',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.parsed.y;
                                if (symbolAfter) {
                                    return value.toLocaleString() + ' ' + currencySymbol;
                                }
                                return currencySymbol + value.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                if (symbolAfter) {
                                    return value.toLocaleString() + ' ' + currencySymbol;
                                }
                                return currencySymbol + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Initialize chart on page load
    initSpendingChart({!! json_encode($spendingTrend) !!});

    // Format currency
    function formatCurrency(amount) {
        const formatted = parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        if (symbolAfter) {
            return formatted + ' ' + currencySymbol;
        }
        return currencySymbol + formatted;
    }

    // Format currency with decimals
    function formatCurrencyDecimals(amount) {
        const formatted = parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (symbolAfter) {
            return formatted + ' ' + currencySymbol;
        }
        return currencySymbol + formatted;
    }

    // Refresh dashboard data
    async function refreshDashboard() {
        const refreshBtn = document.getElementById('refreshBtn');
        const refreshIcon = document.getElementById('refreshIcon');
        const loadingOverlay = document.getElementById('loadingOverlay');
        
        // Show loading state
        refreshBtn.disabled = true;
        refreshIcon.classList.add('animate-spin');
        loadingOverlay.classList.remove('hidden');

        try {
            const response = await fetch('{{ route('dashboard.refresh') }}');
            const data = await response.json();

            // Update monthly stats
            document.querySelector('[data-stat="income"]').textContent = formatCurrency(data.monthlyStats.income);
            document.querySelector('[data-stat="expenses"]').textContent = formatCurrency(data.monthlyStats.expenses);
            document.querySelector('[data-stat="savings"]').textContent = formatCurrency(data.monthlyStats.net_savings);
            document.querySelector('[data-stat="rate"]').textContent = data.monthlyStats.savings_rate + '%';

            // Update net worth
            const netWorthEl = document.querySelector('[data-stat="networth"]');
            netWorthEl.textContent = formatCurrencyDecimals(data.netWorth);
            netWorthEl.className = data.netWorth >= 0 ? 'text-4xl font-bold text-green-600' : 'text-4xl font-bold text-red-600';

            // Update quick stats
            document.querySelector('[data-stat="transactions"]').textContent = data.quickStats.total_transactions;
            document.querySelector('[data-stat="budgets"]').textContent = data.quickStats.total_budgets;
            document.querySelector('[data-stat="active-goals"]').textContent = data.quickStats.active_goals;
            document.querySelector('[data-stat="total-goals"]').textContent = data.quickStats.total_goals;

            // Update recent transactions
            const transactionsContainer = document.getElementById('recentTransactions');
            if (data.recentTransactions.length > 0) {
                transactionsContainer.innerHTML = data.recentTransactions.map(t => `
                    <div class="flex justify-between items-center py-2 border-b last:border-0">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl">${t.category_icon}</span>
                            <div>
                                <p class="font-medium">${t.category_name}</p>
                                <p class="text-sm text-gray-500">${t.date}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="font-semibold ${t.type === 'income' ? 'text-green-600' : 'text-red-600'}">
                                ${t.type === 'income' ? '+' : '-'}${formatCurrencyDecimals(t.amount)}
                            </p>
                            <p class="text-xs text-gray-500">${t.description.substring(0, 20)}${t.description.length > 20 ? '...' : ''}</p>
                        </div>
                    </div>
                `).join('');
            }

            // Update budgets
            const budgetsContainer = document.getElementById('budgetsContainer');
            if (data.budgets.length > 0) {
                budgetsContainer.innerHTML = data.budgets.map(b => {
                    const statusClass = b.utilization.status === 'exceeded' ? 'bg-red-500' : 
                                       b.utilization.status === 'warning' ? 'bg-yellow-500' : 'bg-green-500';
                    return `
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="font-medium">${b.category_icon} ${b.category_name}</span>
                                <span class="text-gray-600">${formatCurrency(b.utilization.spent)} / ${formatCurrency(b.amount)}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all ${statusClass}" style="width: ${Math.min(b.utilization.percentage, 100)}%"></div>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">${b.utilization.percentage}% used</p>
                        </div>
                    `;
                }).join('');
            }

            // Update goals
            const goalsContainer = document.getElementById('goalsContainer');
            if (data.goals.length > 0) {
                goalsContainer.innerHTML = data.goals.map(g => `
                    <div>
                        <div class="flex justify-between text-sm mb-1">
                            <span class="font-medium">${g.name}</span>
                            <span class="text-gray-600">${formatCurrency(g.current_amount)} / ${formatCurrency(g.target_amount)}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full transition-all" style="width: ${Math.min(g.progress.percentage, 100)}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">${g.progress.percentage}% • ${g.progress.days_remaining} days left</p>
                    </div>
                `).join('');
            }

            // Update spending chart
            initSpendingChart(data.spendingTrend);

            // Update last updated time
            document.getElementById('lastUpdated').textContent = 'Last updated: just now';

            // Show success message
            showNotification('Dashboard refreshed successfully!', 'success');

        } catch (error) {
            console.error('Error refreshing dashboard:', error);
            showNotification('Failed to refresh dashboard', 'error');
        } finally {
            // Hide loading state
            refreshBtn.disabled = false;
            refreshIcon.classList.remove('animate-spin');
            loadingOverlay.classList.add('hidden');
        }
    }

    // Show notification
    function showNotification(message, type) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    // Attach refresh button event
    document.getElementById('refreshBtn').addEventListener('click', refreshDashboard);

    // Auto-refresh every 5 minutes
    setInterval(refreshDashboard, 5 * 60 * 1000);

    // Update "last updated" time every minute
    setInterval(() => {
        const lastUpdatedEl = document.getElementById('lastUpdated');
        const currentText = lastUpdatedEl.textContent;
        if (currentText.includes('just now')) {
            lastUpdatedEl.textContent = 'Last updated: 1 minute ago';
        } else {
            const match = currentText.match(/(\d+) minute/);
            if (match) {
                const minutes = parseInt(match[1]) + 1;
                lastUpdatedEl.textContent = `Last updated: ${minutes} minute${minutes > 1 ? 's' : ''} ago`;
            }
        }
    }, 60 * 1000);
</script>
@endsection
