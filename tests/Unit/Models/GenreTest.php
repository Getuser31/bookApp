<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Genre;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use RefreshDatabase;

    public function test_genre_can_be_created(): void
    {
        $genre = Genre::factory()->create([
            'name' => 'Science Fiction',
        ]);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'Science Fiction',
        ]);
    }

    public function test_genre_has_fillable_attributes(): void
    {
        $genre = new Genre();

        $this->assertEquals(['name'], $genre->getFillable());
    }

    public function test_genre_belongs_to_many_books(): void
    {
        $genre = Genre::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $genre->books()->attach([$book1->id, $book2->id]);

        $this->assertCount(2, $genre->books);
    }

    public function test_genre_can_be_updated(): void
    {
        $genre = Genre::factory()->create(['name' => 'Old Genre']);

        $genre->update(['name' => 'New Genre']);

        $this->assertEquals('New Genre', $genre->fresh()->name);
    }

    public function test_genre_can_be_deleted(): void
    {
        $genre = Genre::factory()->create();
        $genreId = $genre->id;

        $genre->delete();

        $this->assertDatabaseMissing('genres', ['id' => $genreId]);
    }

    public function test_genre_name_is_required(): void
    {
        $genre = Genre::factory()->create();

        $this->assertNotNull($genre->name);
        $this->assertNotEmpty($genre->name);
    }
}
