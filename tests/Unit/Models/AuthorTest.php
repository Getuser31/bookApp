<?php

namespace Tests\Unit\Models;

use App\Models\Author;
use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorTest extends TestCase
{
    use RefreshDatabase;

    public function test_author_can_be_created(): void
    {
        $author = Author::factory()->create([
            'name' => 'J.K. Rowling',
        ]);

        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => 'J.K. Rowling',
        ]);
    }

    public function test_author_has_fillable_attributes(): void
    {
        $author = new Author();

        $this->assertEquals(['name'], $author->getFillable());
    }

    public function test_author_can_have_books(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $this->assertTrue($author->books()->exists());
        $this->assertEquals(1, $author->books()->count());
        $this->assertEquals($book->id, $author->books->first()->id);
    }

    public function test_author_name_can_be_updated(): void
    {
        $author = Author::factory()->create(['name' => 'Old Name']);
        $author->update(['name' => 'New Name']);

        $this->assertEquals('New Name', $author->fresh()->name);
    }

    public function test_author_can_be_deleted(): void
    {
        $author = Author::factory()->create();
        $authorId = $author->id;

        $author->delete();

        $this->assertDatabaseMissing('authors', ['id' => $authorId]);
    }

    public function test_author_has_timestamps(): void
    {
        $author = Author::factory()->create();

        $this->assertNotNull($author->created_at);
        $this->assertNotNull($author->updated_at);
    }
}
