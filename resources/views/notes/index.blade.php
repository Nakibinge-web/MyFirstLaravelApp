@extends('layouts.app')

@section('page-title', 'Notes')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Notes</h1>
            <p class="text-sm text-gray-600 mt-1">Write and organize your personal financial reminders and notes.</p>
        </div>
        <a href="{{ route('notes.create') }}" class="inline-flex items-center justify-center bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            + New Note
        </a>
    </div>

    <!-- Search Bar -->
    <div class="bg-white shadow-md rounded px-6 py-4 mb-6">
        <form method="GET" action="{{ route('notes.index') }}" class="flex flex-col sm:flex-row gap-3">
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Search your notes by title or content..."
                   class="flex-1 border rounded px-3 py-2 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <div class="flex gap-2">
                <button type="submit" class="bg-gray-600 hover:bg-gray-800 text-white font-bold py-2 px-4 rounded">
                    Search
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('notes.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-2 px-4 rounded inline-flex items-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    @if($notes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($notes as $note)
                <div class="bg-white shadow-md rounded-lg overflow-hidden flex flex-col justify-between border-t-4 border-blue-500">
                    <div class="p-6">
                        <div class="flex justify-between items-start gap-3 mb-3">
                            <a href="{{ route('notes.show', $note) }}" class="text-lg font-semibold text-gray-900 hover:text-blue-600 transition-colors break-words">
                                {{ $note->title }}
                            </a>
                        </div>

                        <p class="text-sm text-gray-600 whitespace-pre-line break-words line-clamp-4">{{ \Illuminate\Support\Str::limit($note->content, 180) }}</p>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-100 flex items-center justify-between gap-2">
                        <span class="text-xs text-gray-500" title="Updated {{ $note->updated_at->format('M d, Y g:i A') }}">
                            Updated {{ $note->updated_at->diffForHumans() }}
                        </span>

                        <div class="flex items-center space-x-3">
                            <a href="{{ route('notes.show', $note) }}" class="text-gray-700 hover:text-gray-900 text-sm font-medium">View</a>
                            <a href="{{ route('notes.edit', $note) }}" class="text-blue-600 hover:text-blue-900 text-sm font-medium">Edit</a>
                            <button type="button"
                                    onclick="openDeleteNoteModal(this)"
                                    data-delete-url="{{ route('notes.destroy', $note) }}"
                                    data-note-title="{{ $note->title }}"
                                    class="text-red-600 hover:text-red-900 text-sm font-medium">
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($notes->hasPages())
            <div class="mt-6">
                {{ $notes->links() }}
            </div>
        @endif
    @else
        <div class="bg-white shadow-md rounded-lg p-10 text-center">
            @if(request()->filled('search'))
                <h3 class="text-lg font-semibold text-gray-800 mb-2">No matching notes found</h3>
                <p class="text-gray-600 mb-4">No notes matched "{{ request('search') }}".</p>
                <a href="{{ route('notes.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">View all notes</a>
            @else
                <h3 class="text-lg font-semibold text-gray-800 mb-2">No notes yet</h3>
                <p class="text-gray-600 mb-4">Jot down financial ideas, payment reminders, or anything important to revisit later.</p>
                <a href="{{ route('notes.create') }}" class="inline-block bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-5 rounded">
                    + Write Your First Note
                </a>
            @endif
        </div>
    @endif
</div>

@include('components.delete-note-modal')
@endsection
