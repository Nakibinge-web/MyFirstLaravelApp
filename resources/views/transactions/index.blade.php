@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Transactions</h1>
        <a href="{{ route('transactions.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + Add Transaction
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-md rounded px-6 py-4 mb-6">
        <form method="GET" action="{{ route('transactions.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full border rounded px-3 py-2">
                    <option value="">All</option>
                    <option value="income" {{ request('type') == 'income' ? 'selected' : '' }}>Income</option>
                    <option value="expense" {{ request('type') == 'expense' ? 'selected' : '' }}>Expense</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
                <select name="category_id" class="w-full border rounded px-3 py-2">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full border rounded px-3 py-2">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full border rounded px-3 py-2">
            </div>
            
            <div class="flex items-end">
                <button type="submit" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded w-full">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Transactions List -->
    <div class="bg-white shadow-md rounded overflow-hidden">
        @if($transactions->count() > 0)
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($transactions as $transaction)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $transaction->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 rounded text-xs" style="background-color: {{ $transaction->category->color }}20; color: {{ $transaction->category->color }}">
                                    {{ $transaction->category->icon }} {{ $transaction->category->name }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $transaction->description }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <span class="px-2 py-1 rounded text-xs {{ $transaction->type == 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($transaction->type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold {{ $transaction->type == 'income' ? 'text-green-600' : 'text-red-600' }}">
                                {{ $transaction->type == 'income' ? '+' : '-' }}{{ currency_format($transaction->amount) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('transactions.edit', $transaction) }}" class="text-blue-600 hover:text-blue-900 mr-3">Edit</a>
                                <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this transaction?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            
            <div class="px-6 py-4 bg-gray-50">
                {{ $transactions->links() }}
            </div>
        @else
            <div class="px-6 py-12 text-center text-gray-500">
                <p class="text-lg">No transactions found.</p>
                <a href="{{ route('transactions.create') }}" class="text-blue-500 hover:text-blue-700 mt-2 inline-block">
                    Create your first transaction
                </a>
            </div>
        @endif
    </div>

    <!-- Spending Summary Tabs -->
    <div class="mt-8 bg-white shadow-md rounded-lg p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">📊 Spending Summary</h2>
            <div class="flex space-x-2">
                <button onclick="showTab('daily')" id="daily-tab" class="px-4 py-2 bg-blue-600 text-white rounded-lg font-semibold transition-all">
                    Daily
                </button>
                <button onclick="showTab('weekly')" id="weekly-tab" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-all">
                    Weekly
                </button>
            </div>
        </div>

        <!-- Daily Summary Tab -->
        <div id="daily-content" class="tab-content">
            <p class="text-gray-600 mb-6">Select a date to see your spending and income for that day</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Date Selector -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Select Date</label>
                <input 
                    type="date" 
                    id="daily-date" 
                    value="{{ date('Y-m-d') }}"
                    max="{{ date('Y-m-d') }}"
                    class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:outline-none transition-all">
                <button 
                    onclick="loadDailySummary()" 
                    class="mt-3 w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                    <span class="flex items-center justify-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        View Summary
                    </span>
                </button>
            </div>

            <!-- Summary Display -->
            <div id="daily-summary" class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-6 border-2 border-gray-200">
                <div class="text-center text-gray-500">
                    <svg class="w-16 h-16 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-sm">Select a date and click "View Summary" to see your daily transactions</p>
                </div>
            </div>
        </div>

            <!-- Loading State -->
            <div id="loading-summary" class="hidden mt-6 text-center">
                <svg class="animate-spin h-8 w-8 mx-auto text-blue-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600 mt-2">Loading summary...</p>
            </div>
        </div>

        <!-- Weekly Summary Tab -->
        <div id="weekly-content" class="tab-content hidden">
            <p class="text-gray-600 mb-6">Select a week to see your spending and income for that period</p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Week Selector -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Select Week Start Date</label>
                    <input 
                        type="date" 
                        id="weekly-date" 
                        value="{{ date('Y-m-d', strtotime('monday this week')) }}"
                        max="{{ date('Y-m-d') }}"
                        class="w-full border-2 border-gray-300 rounded-lg px-4 py-3 focus:border-blue-500 focus:outline-none transition-all">
                    <p class="text-xs text-gray-500 mt-2">Select any day, and we'll show the full week (Monday-Sunday)</p>
                    <button 
                        onclick="loadWeeklySummary()" 
                        class="mt-3 w-full bg-gradient-to-r from-purple-600 to-pink-600 hover:from-purple-700 hover:to-pink-700 text-white font-bold py-3 px-4 rounded-lg shadow-md hover:shadow-lg transition-all duration-200">
                        <span class="flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            View Weekly Summary
                        </span>
                    </button>
                </div>

                <!-- Summary Display -->
                <div id="weekly-summary" class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg p-6 border-2 border-gray-200">
                    <div class="text-center text-gray-500">
                        <svg class="w-16 h-16 mx-auto mb-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-sm">Select a date and click "View Weekly Summary" to see your weekly transactions</p>
                    </div>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loading-weekly" class="hidden mt-6 text-center">
                <svg class="animate-spin h-8 w-8 mx-auto text-purple-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <p class="text-gray-600 mt-2">Loading weekly summary...</p>
            </div>
        </div>
    </div>
</div>

<script>
// Tab switching
function showTab(tab) {
    const dailyTab = document.getElementById('daily-tab');
    const weeklyTab = document.getElementById('weekly-tab');
    const dailyContent = document.getElementById('daily-content');
    const weeklyContent = document.getElementById('weekly-content');
    
    if (tab === 'daily') {
        dailyTab.classList.remove('bg-gray-200', 'text-gray-700');
        dailyTab.classList.add('bg-blue-600', 'text-white');
        weeklyTab.classList.remove('bg-purple-600', 'text-white');
        weeklyTab.classList.add('bg-gray-200', 'text-gray-700');
        dailyContent.classList.remove('hidden');
        weeklyContent.classList.add('hidden');
    } else {
        weeklyTab.classList.remove('bg-gray-200', 'text-gray-700');
        weeklyTab.classList.add('bg-purple-600', 'text-white');
        dailyTab.classList.remove('bg-blue-600', 'text-white');
        dailyTab.classList.add('bg-gray-200', 'text-gray-700');
        weeklyContent.classList.remove('hidden');
        dailyContent.classList.add('hidden');
    }
}

async function loadDailySummary() {
    const date = document.getElementById('daily-date').value;
    const summaryDiv = document.getElementById('daily-summary');
    const loadingDiv = document.getElementById('loading-summary');
    
    if (!date) {
        alert('Please select a date');
        return;
    }
    
    // Show loading
    summaryDiv.classList.add('hidden');
    loadingDiv.classList.remove('hidden');
    
    try {
        const response = await fetch(`/transactions/daily-summary?date=${date}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        // Hide loading
        loadingDiv.classList.add('hidden');
        summaryDiv.classList.remove('hidden');
        
        // Format date
        const dateObj = new Date(date);
        const formattedDate = dateObj.toLocaleDateString('en-US', { 
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
        
        // Build summary HTML
        summaryDiv.innerHTML = `
            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-800 mb-1">${formattedDate}</h3>
                <p class="text-sm text-gray-600">${data.transaction_count} transaction(s)</p>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Income</p>
                            <p class="text-lg font-bold text-green-700">${data.income_formatted}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Expenses</p>
                            <p class="text-lg font-bold text-red-700">${data.expenses_formatted}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg border-2 border-blue-300">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Net Balance</p>
                            <p class="text-lg font-bold ${data.net_balance >= 0 ? 'text-blue-700' : 'text-red-700'}">${data.net_balance_formatted}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            ${data.transaction_count > 0 ? `
                <div class="mt-4 pt-4 border-t border-gray-300">
                    <p class="text-xs text-gray-500 text-center">
                        ${data.net_balance >= 0 ? '✅ You saved money this day!' : '⚠️ You spent more than you earned this day'}
                    </p>
                </div>
            ` : ''}
        `;
        
    } catch (error) {
        console.error('Error loading summary:', error);
        loadingDiv.classList.add('hidden');
        summaryDiv.classList.remove('hidden');
        summaryDiv.innerHTML = `
            <div class="text-center text-red-500">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p>Error loading summary. Please try again.</p>
            </div>
        `;
    }
}

async function loadWeeklySummary() {
    const date = document.getElementById('weekly-date').value;
    const summaryDiv = document.getElementById('weekly-summary');
    const loadingDiv = document.getElementById('loading-weekly');
    
    if (!date) {
        alert('Please select a date');
        return;
    }
    
    // Show loading
    summaryDiv.classList.add('hidden');
    loadingDiv.classList.remove('hidden');
    
    try {
        const response = await fetch(`/transactions/weekly-summary?date=${date}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        // Hide loading
        loadingDiv.classList.add('hidden');
        summaryDiv.classList.remove('hidden');
        
        // Build summary HTML
        summaryDiv.innerHTML = `
            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-800 mb-1">${data.week_range}</h3>
                <p class="text-sm text-gray-600">${data.transaction_count} transaction(s) this week</p>
            </div>
            
            <div class="space-y-3">
                <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Total Income</p>
                            <p class="text-lg font-bold text-green-700">${data.income_formatted}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Total Expenses</p>
                            <p class="text-lg font-bold text-red-700">${data.expenses_formatted}</p>
                        </div>
                    </div>
                </div>
                
                <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg border-2 border-purple-300">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-purple-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Net Balance</p>
                            <p class="text-lg font-bold ${data.net_balance >= 0 ? 'text-purple-700' : 'text-red-700'}">${data.net_balance_formatted}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-600">Daily Average</p>
                            <p class="text-lg font-bold text-blue-700">${data.daily_average_formatted}</p>
                        </div>
                    </div>
                </div>
            </div>
            
            ${data.transaction_count > 0 ? `
                <div class="mt-4 pt-4 border-t border-gray-300">
                    <p class="text-xs text-gray-500 text-center">
                        ${data.net_balance >= 0 ? '✅ You saved money this week!' : '⚠️ You spent more than you earned this week'}
                    </p>
                </div>
            ` : ''}
        `;
        
    } catch (error) {
        console.error('Error loading weekly summary:', error);
        loadingDiv.classList.add('hidden');
        summaryDiv.classList.remove('hidden');
        summaryDiv.innerHTML = `
            <div class="text-center text-red-500">
                <svg class="w-12 h-12 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p>Error loading weekly summary. Please try again.</p>
            </div>
        `;
    }
}
</script>

@endsection
