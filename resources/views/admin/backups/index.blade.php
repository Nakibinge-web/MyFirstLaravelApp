@extends('layouts.admin')

@section('page-title', 'Backup Management')

@section('content')
{{-- Header + Create Button --}}
<div class="flex items-center justify-between mb-6">
    <div>
        <p class="text-sm text-gray-500 mt-1">Manage database backups. Up to 10 completed backups are retained automatically.</p>
    </div>
    <form method="POST" action="{{ route('admin.backups.create') }}"
          onsubmit="return confirm('Create a new database backup now?')">
        @csrf
        <button type="submit"
                class="inline-flex items-center space-x-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
            </svg>
            <span>Create Backup</span>
        </button>
    </form>
</div>

{{-- Backups Table --}}
<div class="bg-white rounded-lg shadow overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-base font-semibold text-gray-800">
            Backups
            <span class="ml-2 text-sm font-normal text-gray-500">({{ $backups->count() }} total)</span>
        </h2>
    </div>

    <div class="overflow-x-auto">
        @if($backups->isEmpty())
        <div class="px-6 py-12 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
            </svg>
            <p class="text-sm">No backups found. Create your first backup above.</p>
        </div>
        @else
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filename</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Size</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created By</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created At</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($backups as $backup)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4"></path>
                            </svg>
                            <span class="font-mono text-xs text-gray-700">{{ $backup->filename }}</span>
                        </div>
                        @if($backup->description)
                            <p class="text-xs text-gray-400 mt-0.5 ml-6">{{ $backup->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if($backup->status === 'completed')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                ✓ Completed
                            </span>
                        @elseif($backup->status === 'pending')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                ⏳ Pending
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                ✗ Failed
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $backup->getFormattedSize() }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $backup->creator ? $backup->creator->name : '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-500 whitespace-nowrap">
                        <span title="{{ $backup->created_at->format('Y-m-d H:i:s') }}">
                            {{ $backup->created_at->format('M d, Y H:i') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end space-x-2">
                            {{-- Download (only for completed backups) --}}
                            @if($backup->status === 'completed')
                            <a href="{{ route('admin.backups.download', $backup->id) }}"
                               class="px-3 py-1 text-xs bg-blue-50 text-blue-700 rounded hover:bg-blue-100 transition-colors inline-flex items-center space-x-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                <span>Download</span>
                            </a>
                            @endif

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.backups.delete', $backup->id) }}"
                                  onsubmit="return confirm('Are you sure you want to permanently delete this backup?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="px-3 py-1 text-xs bg-red-50 text-red-700 rounded hover:bg-red-100 transition-colors inline-flex items-center space-x-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                    <span>Delete</span>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>
</div>

{{-- Info Box --}}
<div class="mt-4 bg-blue-50 border border-blue-200 rounded-lg p-4 text-sm text-blue-700">
    <div class="flex items-start space-x-2">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <div>
            <p class="font-medium">Backup Retention Policy</p>
            <p class="mt-1 text-blue-600">The system automatically retains the 10 most recent completed backups. Older backups are deleted automatically when a new backup is created.</p>
        </div>
    </div>
</div>
@endsection
