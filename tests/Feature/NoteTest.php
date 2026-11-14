<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login_from_notes_routes(): void
    {
        $note = Note::factory()->create();

        $this->get(route('notes.index'))->assertRedirect(route('login'));
        $this->get(route('notes.create'))->assertRedirect(route('login'));
        $this->post(route('notes.store'), [
            'title' => 'Guest Note',
            'content' => 'Should not be saved.',
        ])->assertRedirect(route('login'));
        $this->get(route('notes.show', $note))->assertRedirect(route('login'));
        $this->get(route('notes.edit', $note))->assertRedirect(route('login'));
        $this->put(route('notes.update', $note), [
            'title' => 'Updated',
            'content' => 'Updated content',
        ])->assertRedirect(route('login'));
        $this->delete(route('notes.destroy', $note))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_sees_notes_link_below_categories_in_sidebar(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('notes.index'));

        $response->assertStatus(200);

        $html = $response->getContent();
        $categoriesPos = strpos($html, route('categories.index'));
        $notesPos = strpos($html, route('notes.index'));

        $this->assertNotFalse($categoriesPos);
        $this->assertNotFalse($notesPos);
        $this->assertGreaterThan($categoriesPos, $notesPos);
    }

    public function test_user_can_create_view_edit_and_delete_a_note(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('notes.create'))->assertStatus(200);

        $createResponse = $this->actingAs($user)->post(route('notes.store'), [
            'title' => 'Emergency Fund Plan',
            'content' => 'Save three months of rent and utility bills.',
        ]);

        $createResponse->assertRedirect(route('notes.index'));
        $createResponse->assertSessionHas('success', 'Note saved successfully.');

        $this->assertDatabaseHas('notes', [
            'user_id' => $user->id,
            'title' => 'Emergency Fund Plan',
            'content' => 'Save three months of rent and utility bills.',
        ]);

        $note = Note::forUser((int) $user->id)->firstOrFail();

        $this->actingAs($user)
            ->get(route('notes.index'))
            ->assertStatus(200)
            ->assertSee('Emergency Fund Plan');

        $this->actingAs($user)
            ->get(route('notes.show', $note))
            ->assertStatus(200)
            ->assertSee('Emergency Fund Plan')
            ->assertSee('Save three months of rent and utility bills.');

        $this->actingAs($user)
            ->get(route('notes.edit', $note))
            ->assertStatus(200)
            ->assertSee('Emergency Fund Plan')
            ->assertSee('Save three months of rent and utility bills.');

        $updateResponse = $this->actingAs($user)->put(route('notes.update', $note), [
            'title' => 'Updated Emergency Fund Plan',
            'content' => 'Target six months of living expenses in a high-yield account.',
        ]);

        $updateResponse->assertRedirect(route('notes.show', $note));
        $updateResponse->assertSessionHas('success', 'Note updated successfully.');

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'user_id' => $user->id,
            'title' => 'Updated Emergency Fund Plan',
            'content' => 'Target six months of living expenses in a high-yield account.',
        ]);

        $deleteResponse = $this->actingAs($user)->delete(route('notes.destroy', $note));

        $deleteResponse->assertRedirect(route('notes.index'));
        $deleteResponse->assertSessionHas('success', 'Note deleted successfully.');

        $this->assertDatabaseMissing('notes', [
            'id' => $note->id,
        ]);
    }

    public function test_note_validation_requires_title_and_content(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('notes.store'), [
                'title' => '',
                'content' => '',
            ])
            ->assertSessionHasErrors(['title', 'content']);

        $this->actingAs($user)
            ->post(route('notes.store'), [
                'title' => str_repeat('a', 151),
                'content' => 'Valid content',
            ])
            ->assertSessionHasErrors(['title']);
    }

    public function test_note_output_is_escaped_to_prevent_xss(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('notes.store'), [
            'title' => '<script>alert("xss-title")</script>',
            'content' => '<script>alert("xss-body")</script>',
        ])->assertRedirect(route('notes.index'));

        $note = Note::forUser((int) $user->id)->firstOrFail();

        $this->actingAs($user)
            ->get(route('notes.index'))
            ->assertStatus(200)
            ->assertDontSee('<script>alert("xss-title")</script>', false)
            ->assertSee('<script>alert("xss-title")</script>');

        $this->actingAs($user)
            ->get(route('notes.show', $note))
            ->assertStatus(200)
            ->assertDontSee('<script>alert("xss-body")</script>', false)
            ->assertSee('<script>alert("xss-body")</script>');
    }

    public function test_notes_index_and_search_are_strictly_scoped_to_authenticated_user(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        Note::factory()->for($userA)->create([
            'title' => 'User A BudgetAlpha',
            'content' => 'SharedKeyword note owned by user A.',
        ]);

        Note::factory()->for($userA)->create([
            'title' => 'User A GroceryNotes',
            'content' => 'Weekly grocery shopping checklist.',
        ]);

        Note::factory()->for($userB)->create([
            'title' => 'User B SecretBeta',
            'content' => 'HiddenTokenGamma and SharedKeyword belonging exclusively to user B.',
        ]);

        $this->actingAs($userA)
            ->get(route('notes.index'))
            ->assertStatus(200)
            ->assertSee('User A BudgetAlpha')
            ->assertSee('User A GroceryNotes')
            ->assertDontSee('User B SecretBeta');

        // Matching User B's title must return zero notes for User A.
        $this->actingAs($userA)
            ->get(route('notes.index', ['search' => 'SecretBeta']))
            ->assertStatus(200)
            ->assertViewHas('notes', fn ($notes) => $notes->isEmpty())
            ->assertSee('No matching notes found')
            ->assertDontSee('User B SecretBeta')
            ->assertDontSee('belonging exclusively to user B.');

        // Matching User B's content via orWhere('content', ...) must stay scoped to User A.
        $this->actingAs($userA)
            ->get(route('notes.index', ['search' => 'HiddenTokenGamma']))
            ->assertStatus(200)
            ->assertViewHas('notes', fn ($notes) => $notes->isEmpty())
            ->assertSee('No matching notes found')
            ->assertDontSee('User B SecretBeta')
            ->assertDontSee('belonging exclusively to user B.');

        // Shared keyword across both tenants must return only User A's note.
        $this->actingAs($userA)
            ->get(route('notes.index', ['search' => 'SharedKeyword']))
            ->assertStatus(200)
            ->assertViewHas('notes', fn ($notes) => $notes->count() === 1)
            ->assertSee('User A BudgetAlpha')
            ->assertDontSee('User B SecretBeta');

        // Searching within User A's notes filters correctly.
        $this->actingAs($userA)
            ->get(route('notes.index', ['search' => 'BudgetAlpha']))
            ->assertStatus(200)
            ->assertViewHas('notes', fn ($notes) => $notes->count() === 1)
            ->assertSee('User A BudgetAlpha')
            ->assertDontSee('User A GroceryNotes');

        // Literal % search is escaped and never returns another tenant's notes.
        $this->actingAs($userA)
            ->get(route('notes.index', ['search' => '%']))
            ->assertStatus(200)
            ->assertViewHas('notes', fn ($notes) => $notes->isEmpty())
            ->assertSee('No matching notes found')
            ->assertDontSee('User A BudgetAlpha')
            ->assertDontSee('User B SecretBeta');
    }

    public function test_user_cannot_view_edit_update_or_delete_another_users_note(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $userBNote = Note::factory()->for($userB)->create([
            'title' => 'Private User B Title',
            'content' => 'Private User B Content',
        ]);

        $this->actingAs($userA)
            ->get(route('notes.show', $userBNote))
            ->assertStatus(403);

        $this->actingAs($userA)
            ->get(route('notes.edit', $userBNote))
            ->assertStatus(403);

        $this->actingAs($userA)
            ->put(route('notes.update', $userBNote), [
                'title' => 'Tampered Title',
                'content' => 'Tampered Content',
            ])
            ->assertStatus(403);

        $this->actingAs($userA)
            ->patch(route('notes.update', $userBNote), [
                'title' => 'Tampered Title',
                'content' => 'Tampered Content',
            ])
            ->assertStatus(403);

        $this->actingAs($userA)
            ->delete(route('notes.destroy', $userBNote))
            ->assertStatus(403);

        $this->assertDatabaseHas('notes', [
            'id' => $userBNote->id,
            'user_id' => $userB->id,
            'title' => 'Private User B Title',
            'content' => 'Private User B Content',
        ]);
    }

    public function test_mass_assignment_cannot_spoof_note_ownership(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->actingAs($userA)->post(route('notes.store'), [
            'user_id' => $userB->id,
            'title' => 'User A Note With Spoofed Owner',
            'content' => 'Must remain owned by User A.',
        ])->assertRedirect(route('notes.index'));

        $this->assertDatabaseHas('notes', [
            'user_id' => $userA->id,
            'title' => 'User A Note With Spoofed Owner',
        ]);
        $this->assertDatabaseMissing('notes', [
            'user_id' => $userB->id,
            'title' => 'User A Note With Spoofed Owner',
        ]);

        $noteA = Note::forUser((int) $userA->id)->firstOrFail();

        $this->actingAs($userA)->put(route('notes.update', $noteA), [
            'user_id' => $userB->id,
            'title' => 'Still Owned By User A',
            'content' => 'Updated body.',
        ])->assertRedirect(route('notes.show', $noteA));

        $this->assertDatabaseHas('notes', [
            'id' => $noteA->id,
            'user_id' => $userA->id,
            'title' => 'Still Owned By User A',
        ]);
    }

    public function test_notes_views_render_delete_confirmation_modal_instead_of_browser_confirm_alert(): void
    {
        $user = User::factory()->create();
        $note = Note::factory()->for($user)->create([
            'title' => 'Delete Modal "Quoted" Note',
        ]);

        $deleteUrl = route('notes.destroy', $note);

        foreach ([route('notes.index'), route('notes.show', $note)] as $url) {
            $this->actingAs($user)
                ->get($url)
                ->assertStatus(200)
                ->assertDontSee('return confirm(', false)
                ->assertSee('id="delete-note-modal"', false)
                ->assertSee('id="delete-note-form"', false)
                ->assertSee('openDeleteNoteModal(this)', false)
                ->assertSee('data-delete-url="' . $deleteUrl . '"', false)
                ->assertSee('data-note-title="Delete Modal &quot;Quoted&quot; Note"', false)
                ->assertSee('Are you sure you want to delete this note?')
                ->assertSee('Yes, Delete');
        }
    }
}
