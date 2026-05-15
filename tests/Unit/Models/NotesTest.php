<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Notes;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotesTest extends TestCase
{
    use RefreshDatabase;

    public function test_note_can_be_created(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $note = Notes::create([
            'content' => 'This is a great book!',
            'user_id' => $user->id,
            'book_id' => $book->id,
        ]);

        $this->assertDatabaseHas('notes', [
            'id' => $note->id,
            'content' => 'This is a great book!',
        ]);
    }

    public function test_note_belongs_to_user(): void
    {
        $note = Notes::factory()->create();

        $this->assertInstanceOf(User::class, $note->user);
    }

    public function test_note_belongs_to_book(): void
    {
        $note = Notes::factory()->create();

        $this->assertInstanceOf(Book::class, $note->book);
    }

    public function test_get_notes_for_book_and_user(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $note1 = Notes::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'content' => 'Note 1',
        ]);
        $note2 = Notes::factory()->create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'content' => 'Note 2',
        ]);

        $notes = Notes::getNotesForBookAndUser($user->id, $book->id);

        $this->assertCount(2, $notes);
        $this->assertEquals('Note 1', $notes[0]->content);
        $this->assertEquals('Note 2', $notes[1]->content);
    }

    public function test_get_notes_returns_empty_when_no_notes(): void
    {
        $notes = Notes::getNotesForBookAndUser(999, 999);

        $this->assertCount(0, $notes);
    }

    public function test_note_has_fillable_attributes(): void
    {
        $note = new Notes();

        $this->assertEquals(['content', 'user_id', 'book_id'], $note->getFillable());
    }
}
