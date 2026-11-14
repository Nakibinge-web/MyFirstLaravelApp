<?php

namespace App\Http\Controllers;

use App\Http\Requests\NoteRequest;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = Note::forUser((int) $request->user()->id);

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $escapedSearch = addcslashes($search, '%_\\');
            // Group OR conditions so tenant scoping cannot be bypassed by OR precedence.
            $query->where(function ($q) use ($escapedSearch) {
                $q->where('title', 'like', "%{$escapedSearch}%")
                    ->orWhere('content', 'like', "%{$escapedSearch}%");
            });
        }

        $notes = $query
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(12)
            ->withQueryString();

        return view('notes.index', compact('notes'));
    }

    public function create()
    {
        return view('notes.create');
    }

    public function store(NoteRequest $request)
    {
        $request->user()->notes()->create($request->validated());

        return redirect()->route('notes.index')->with('success', 'Note saved successfully.');
    }

    public function show(Note $note)
    {
        $this->authorize('view', $note);

        return view('notes.show', compact('note'));
    }

    public function edit(Note $note)
    {
        $this->authorize('update', $note);

        return view('notes.edit', compact('note'));
    }

    public function update(NoteRequest $request, Note $note)
    {
        $this->authorize('update', $note);

        $note->update($request->validated());

        return redirect()->route('notes.show', $note)->with('success', 'Note updated successfully.');
    }

    public function destroy(Note $note)
    {
        $this->authorize('delete', $note);

        $note->delete();

        return redirect()->route('notes.index')->with('success', 'Note deleted successfully.');
    }
}
