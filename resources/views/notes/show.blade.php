@extends('layouts.app')

@section('page-title', 'View Note')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <x-back-button :route="route('notes.index')" label="Back to Notes" />
    </div>

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6 pb-4 border-b border-gray-200">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 break-words">{{ $note->title }}</h1>
                    <div class="mt-2 text-xs text-gray-500 space-x-3">
                        <span>Created: {{ $note->created_at->format('M d, Y g:i A') }}</span>
                        <span>Last updated: {{ $note->updated_at->format('M d, Y g:i A') }}</span>
                    </div>
                </div>

                <div class="flex items-center space-x-2 shrink-0">
                    <a href="{{ route('notes.edit', $note) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Edit Note
                    </a>
                    <button type="button"
                            onclick="openDeleteNoteModal(this)"
                            data-delete-url="{{ route('notes.destroy', $note) }}"
                            data-note-title="{{ $note->title }}"
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Delete
                    </button>
                </div>
            </div>

            <div class="text-gray-800 leading-relaxed whitespace-pre-wrap break-words">{{ $note->content }}</div>
        </div>
    </div>
</div>

@include('components.delete-note-modal')
@endsection
