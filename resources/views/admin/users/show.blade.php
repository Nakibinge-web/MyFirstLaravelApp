@extends('layouts.admin')

@section('page-title', 'User Details')

@section('content')
{{-- Back + Header --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('admin.users') }}" class="flex items-center space-x-2 text-sm text-gray-500 hover:text-gray-700">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
        </svg>
        <span>Back to Users</span>
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left Column: Profile + Actions --}}
    <div class="space-y-6">
        {{-- Profile Card --}}
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex flex-col items-center text-center mb-4">
                <div class="w-16 h-16 rounded-full {{ $user->is_admin ? 'bg-red-600' : 'bg-blue-600' }} flex items-center justify-center text-white text-2xl font-bold mb-3">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <h2 class="text-lg font-semibold text-gray-800">{{ $user->name }}</h2>
                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                <div class="flex items-center space-x-2 mt-2">
                    @if($user->is_admin)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Admin</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">User</span>
                    @endif
                    @if($user->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Inactive</span>
                    @endif
                </div>
            </div>

            <div class="space-y-2 text-sm border-t border-gray-100 pt-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">Currency</span>
                    <span class="font-medium text-gray-800">{{ $user->currency ?? 'USD' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Joined</span>
                    <span class="font-medium text-gray-800">{{ $user->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Account Age</span>
                    <span class="font-medium text-gray-800">{{ $statistics['account_age_days'] }} days</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Last Login</span>
                    <span class="font-medium text-gray-800">
                        {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Never' }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Actions Card --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-sm font-semibold text-gray-700 mb-4">Actions</h3>
            <div class="space-y-2">
                {{-- Toggle Status --}}
                <form method="POST" action="{{ route('admin.users.toggle-status', $user->id) }}"
                      onsubmit="return confirm('Are you sure you want to {{ $user->is_active ? 'deactivate' : 'activate' }} this user?')">
                    @csrf
                    <button type="submit"
                            class="w-full px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ $user->is_active ? 'bg-red-50 text-red-700 hover:bg-red-100 border border-red-200' : 'bg-green-50 text-green-700 hover:bg-green-100 border border-green-200' }}">
                        {{ $user->is_active ? '🚫 Deactivate Account' : '✅ Activate Account' }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Right Column: Stats + Activity --}}
    <div class="lg:col-span-2 space-y-6">
        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-2xl font-bold text-gray-800">{{ number_format($statistics['transactions']['total']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Transactions</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-2xl font-bold text-gray-800">{{ number_format($statistics['budgets']['total']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Budgets</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-2xl font-bold text-gray-800">{{ number_format($statistics['goals']['total']) }}</p>
                <p class="text-xs text-gray-500 mt-1">Goals</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4 text-center">
                <p class="text-2xl font-bold text-gray-800">{{ number_format($statistics['goals']['completion_rate'], 0) }}%</p>
                <p class="text-xs text-gray-500 mt-1">Goal Rate</p>
            </div>
        </div>

        {{-- Financial Summary --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-800">Financial Summary</h3>
            </div>
            <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="text-center p-4 bg-green-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">Total Income</p>
                    <p class="text-xl font-bold text-green-600">${{ number_format($statistics['transactions']['income'], 2) }}</p>
                </div>
                <div class="text-center p-4 bg-red-50 rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">Total Expenses</p>
                    <p class="text-xl font-bold text-red-600">${{ number_format($statistics['transactions']['expenses'], 2) }}</p>
                </div>
                <div class="text-center p-4 {{ $statistics['transactions']['net'] >= 0 ? 'bg-blue-50' : 'bg-orange-50' }} rounded-lg">
                    <p class="text-sm text-gray-500 mb-1">Net Amount</p>
                    <p class="text-xl font-bold {{ $statistics['transactions']['net'] >= 0 ? 'text-blue-600' : 'text-orange-600' }}">
                        ${{ number_format(abs($statistics['transactions']['net']), 2) }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Budget & Goal Details --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Budgets</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total</span>
                        <span class="font-medium">{{ $statistics['budgets']['total'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Active</span>
                        <span class="font-medium text-green-600">{{ $statistics['budgets']['active'] }}</span>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-5">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Goals</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Total</span>
                        <span class="font-medium">{{ $statistics['goals']['total'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Completed</span>
                        <span class="font-medium text-green-600">{{ $statistics['goals']['completed'] }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500">Completion Rate</span>
                        <span class="font-medium">{{ number_format($statistics['goals']['completion_rate'], 1) }}%</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-semibold text-gray-800">Recent Activity</h3>
            </div>
            @if($recentActivity->isEmpty())
            <div class="px-6 py-8 text-center text-gray-400 text-sm">No activity recorded for this user.</div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($recentActivity as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $log->description }}</td>
                            <td class="px-4 py-3 text-gray-400 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
