<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Collection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CollectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_collection_can_be_created(): void
    {
        $collection = Collection::factory()->create([
            'name' => 'Best Sellers',
        ]);

        $this->assertDatabaseHas('collections', [
            'id' => $collection->id,
            'name' => 'Best Sellers',
        ]);
    }

    public function test_collection_has_many_books(): void
    {
        $collection = Collection::factory()->create();
        $book1 = Book::factory()->create(['collection_id' => $collection->id]);
        $book2 = Book::factory()->create(['collection_id' => $collection->id]);

        $this->assertCount(2, $collection->books);
        $this->assertTrue($collection->books->contains($book1));
        $this->assertTrue($collection->books->contains($book2));
    }

    public function test_collection_can_be_deleted(): void
    {
        $collection = Collection::factory()->create();
        $collectionId = $collection->id;

        $collection->delete();

        $this->assertDatabaseMissing('collections', ['id' => $collectionId]);
    }
}
