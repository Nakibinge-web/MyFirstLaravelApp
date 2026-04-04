@extends('layouts.admin')

@section('page-title', 'Activity Logs')

@section('content')
{{-- Filter Form --}}
<div class="bg-white rounded-lg shadow mb-6 p-4">
    <form method="GET" action="{{ route('admin.activity-logs') }}" class="flex flex-wrap gap-3 items-end">
        {{-- Search --}}
        <div class="flex-1 min-w-48">
            <label class="block text-xs font-medium text-gray-600 mb-1">Search Description</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}"
                   placeholder="Search log descriptions..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Action Filter --}}
        <div class="min-w-44">
            <label class="block text-xs font-medium text-gray-600 mb-1">Action</label>
            <select name="action" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">All actions</option>
                @foreach($actions as $action)
                    <option value="{{ $action }}" {{ ($filters['action'] ?? '') === $action ? 'selected' : '' }}>
                        {{ $action }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- User ID --}}
        <div class="min-w-32">
            <label class="block text-xs font-medium text-gray-600 mb-1">User ID</label>
            <input type="number" name="user_id" value="{{ $filters['user_id'] ?? '' }}"
                   placeholder="User ID..."
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Date From --}}
        <div class="min-w-36">
            <label class="block text-xs font-medium text-gray-600 mb-1">From Date</label>
            <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        {{-- Date To --}}
        <div class="min-w-36">
            <label class="block text-xs font-medium text-gray-600 mb-1">To Date</label>
            <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                   class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>

        <div class="flex gap-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                Filter
            </button>
            <a href="{{ route('admin.activity-logs') }}" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">
                Reset
            </a>
        </div>
    </form>
</div>

{{-- Logs Table --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-base font-semibold text-gray-800">
            Activity Logs
            <span class="ml-2 text-sm font-normal text-gray-500">({{ $logs->total() }} total)</span>
        </h2>
    </div>

    <div class="overflow-x-auto">
        @if($logs->isEmpty())
        <div class="px-6 py-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            <p class="text-sm">No activity logs found matching your filters.</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($logs as $log)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        @php
                            $actionColors = [
                                'user_login'       => 'bg-blue-100 text-blue-800',
                                'user_activated'   => 'bg-green-100 text-green-800',
                                'user_deactivated' => 'bg-red-100 text-red-800',
                                'backup_created'   => 'bg-purple-100 text-purple-800',
                                'backup_failed'    => 'bg-red-100 text-red-800',
                                'backup_downloaded'=> 'bg-indigo-100 text-indigo-800',
                                'admin_promoted'   => 'bg-yellow-100 text-yellow-800',
                                'admin_revoked'    => 'bg-orange-100 text-orange-800',
                                'setting_updated'  => 'bg-teal-100 text-teal-800',
                            ];
                            $colorClass = $actionColors[$log->action] ?? 'bg-gray-100 text-gray-700';
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $colorClass }}">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($log->user)
                            <a href="{{ route('admin.users.show', $log->user_id) }}" class="text-blue-600 hover:text-blue-800">
                                {{ $log->user->name }}
                            </a>
                        @else
                            <span class="text-gray-400 italic">System</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 max-w-sm">
                        <span title="{{ $log->description }}" class="block truncate">{{ $log->description }}</span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $log->ip_address ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                        <span title="{{ $log->created_at->format('Y-m-d H:i:s') }}">
                            {{ $log->created_at->format('M d, Y H:i') }}
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="px-6 py-4 border-t border-gray-200">
        {{ $logs->appends($filters)->links() }}
    </div>
    @endif
</div>
@endsection
