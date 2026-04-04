@extends('layouts.admin')

@section('page-title', 'Dashboard')

@section('content')
{{-- Metric Cards Row 1: Users & Transactions --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Total Users --}}
    <div class="bg-white rounded-lg shadow p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Users</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($metrics['users']['total']) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ number_format($metrics['users']['new_this_month']) }} new this month</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Active Users --}}
    <div class="bg-white rounded-lg shadow p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Active Users</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($metrics['users']['active']) }}</p>
                <p class="text-xs text-gray-400 mt-1">Logged in last 30 days</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Total Transactions --}}
    <div class="bg-white rounded-lg shadow p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Total Transactions</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">{{ number_format($metrics['transactions']['total']) }}</p>
                <p class="text-xs text-gray-400 mt-1">{{ number_format($metrics['transactions']['this_month']) }} this month</p>
            </div>
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
        </div>
    </div>

    {{-- Transaction Volume --}}
    <div class="bg-white rounded-lg shadow p-5">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Transaction Volume</p>
                <p class="text-3xl font-bold text-gray-800 mt-1">${{ number_format($metrics['transactions']['volume'], 2) }}</p>
                <p class="text-xs text-gray-400 mt-1">All time total</p>
            </div>
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
        </div>
    </div>
</div>

{{-- Financial Summary Row --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    {{-- Total Income --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
        <p class="text-sm text-gray-500">Total Income</p>
        <p class="text-2xl font-bold text-green-600 mt-1">${{ number_format($metrics['financial']['total_income'], 2) }}</p>
    </div>

    {{-- Total Expenses --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 border-red-500">
        <p class="text-sm text-gray-500">Total Expenses</p>
        <p class="text-2xl font-bold text-red-600 mt-1">${{ number_format($metrics['financial']['total_expenses'], 2) }}</p>
    </div>

    {{-- Net Amount --}}
    <div class="bg-white rounded-lg shadow p-5 border-l-4 {{ $metrics['financial']['net'] >= 0 ? 'border-blue-500' : 'border-orange-500' }}">
        <p class="text-sm text-gray-500">Net Amount</p>
        <p class="text-2xl font-bold {{ $metrics['financial']['net'] >= 0 ? 'text-blue-600' : 'text-orange-600' }} mt-1">
            ${{ number_format(abs($metrics['financial']['net']), 2) }}
            <span class="text-sm font-normal">{{ $metrics['financial']['net'] >= 0 ? 'surplus' : 'deficit' }}</span>
        </p>
    </div>
</div>

{{-- Bottom Row: Recent Activity + System Health --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Recent Activity --}}
    <div class="lg:col-span-2 bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Recent Activity</h2>
            <a href="{{ route('admin.activity-logs') }}" class="text-sm text-blue-600 hover:text-blue-800">View all</a>
        </div>
        <div class="overflow-x-auto">
            @if($metrics['recent_activity']->isEmpty())
            <div class="px-6 py-8 text-center text-gray-400 text-sm">No activity recorded yet.</div>
            @else
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($metrics['recent_activity'] as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                {{ $log->action }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            {{ $log->user ? $log->user->name : 'System' }}
                        </td>
                        <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $log->description }}</td>
                        <td class="px-4 py-3 text-gray-400 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- System Health --}}
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-800">System Health</h2>
        </div>
        <div class="p-6 space-y-4">
            {{-- Database Size --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600">Database Size</span>
                </div>
                <span class="text-sm font-medium text-gray-800">{{ $metrics['system']['database_size'] }}</span>
            </div>

            {{-- Backup Count --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600">Backups</span>
                </div>
                <span class="text-sm font-medium text-gray-800">{{ $metrics['system']['backup_count'] }} completed</span>
            </div>

            {{-- Last Backup --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <span class="text-sm text-gray-600">Last Backup</span>
                </div>
                <span class="text-sm font-medium text-gray-800">
                    @if($metrics['system']['last_backup'])
                        {{ $metrics['system']['last_backup']->created_at->diffForHumans() }}
                    @else
                        <span class="text-gray-400">Never</span>
                    @endif
                </span>
            </div>

            <div class="pt-4 border-t border-gray-100">
                <a href="{{ route('admin.backups') }}"
                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Manage Backups
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
