@extends('layouts.app')

@section('page-title', 'Edit Note')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="mb-6">
        <x-back-button :route="route('notes.show', $note)" label="Back to Note" />
    </div>

    <div class="bg-white shadow-md rounded px-8 pt-6 pb-8">
        <h2 class="text-2xl font-bold mb-6 text-gray-900">Edit Note</h2>

        <form method="POST" action="{{ route('notes.update', $note) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="title">
                    Title <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       name="title"
                       id="title"
                       value="{{ old('title', $note->title) }}"
                       maxlength="150"
                       class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror"
                       placeholder="e.g., Monthly bills checklist"
                       required>
                @error('title')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="content">
                    Note Content <span class="text-red-500">*</span>
                </label>
                <textarea name="content"
                          id="content"
                          rows="8"
                          maxlength="10000"
                          class="shadow border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('content') border-red-500 @enderror"
                          placeholder="Write your note here..."
                          required>{{ old('content', $note->content) }}</textarea>
                @error('content')
                    <p class="text-red-500 text-xs italic mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('notes.show', $note) }}" class="text-gray-500 hover:text-gray-700">Cancel</a>
                <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded">
                    Update Note
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
