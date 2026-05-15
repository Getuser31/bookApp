<?php

namespace Tests\Unit\Models;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookRating;
use App\Models\Collection;
use App\Models\Genre;
use App\Models\Notes;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_can_be_created(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create([
            'title' => 'Test Book Title',
            'author_id' => $author->id,
        ]);

        $this->assertDatabaseHas('books', [
            'id' => $book->id,
            'title' => 'Test Book Title',
        ]);
    }

    public function test_book_belongs_to_author(): void
    {
        $author = Author::factory()->create();
        $book = Book::factory()->create(['author_id' => $author->id]);

        $this->assertInstanceOf(Author::class, $book->author);
        $this->assertEquals($author->id, $book->author->id);
    }

    public function test_book_belongs_to_many_users(): void
    {
        $book = Book::factory()->create();
        $user = User::factory()->create();

        $user->books()->attach($book, ['progression' => 50, 'favorite' => false]);

        $this->assertTrue($book->users()->exists());
        $this->assertEquals(1, $book->users()->count());
        $this->assertEquals(50, $book->users->first()->pivot->progression);
    }

    public function test_book_belongs_to_many_genres(): void
    {
        $book = Book::factory()->create();
        $genre1 = Genre::factory()->create();
        $genre2 = Genre::factory()->create();

        $book->genres()->attach([$genre1->id, $genre2->id]);

        $this->assertCount(2, $book->genres);
    }

    public function test_book_has_many_ratings(): void
    {
        $book = Book::factory()->create();
        $rating = BookRating::factory()->create(['book_id' => $book->id]);

        $this->assertTrue($book->ratings()->exists());
        $this->assertNotNull($book->ratings->first()->id);
        $this->assertEquals($book->id, $book->ratings->first()->book_id);
    }

    public function test_book_belongs_to_collection(): void
    {
        $collection = Collection::factory()->create();
        $book = Book::factory()->create(['collection_id' => $collection->id]);

        $this->assertInstanceOf(Collection::class, $book->collection);
        $this->assertEquals($collection->id, $book->collection->id);
    }

    public function test_get_formatted_date_with_valid_date(): void
    {
        $book = new Book();
        $formattedDate = $book->getFormattedDate('2024-01-15');

        $this->assertEquals('2024-01-15', $formattedDate);
    }

    public function test_get_formatted_date_with_dmy_format(): void
    {
        $book = new Book();
        $formattedDate = $book->getFormattedDate('15/01/2024');

        $this->assertEquals('2024-01-15', $formattedDate);
    }

    public function test_get_formatted_date_throws_exception_on_invalid_date(): void
    {
        $this->expectException(\Error::class);

        $book = new Book();
        $book->getFormattedDate('invalid-date');
    }

    public function test_books_started_returns_correct_count(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();

        $user->books()->attach($book1, ['progression' => 30]);
        $user->books()->attach($book2, ['progression' => 50]);
        $user->books()->attach($book3, ['progression' => 0]); // not started

        $started = Book::BooksStarted($user->id);

        $this->assertEquals(2, $started);
    }

    public function test_books_finished_returns_correct_count(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $user->books()->attach($book1, ['progression' => 100]);
        $user->books()->attach($book2, ['progression' => 50]);

        $finished = Book::BooksFinished($user->id);

        $this->assertEquals(1, $finished);
    }

    public function test_books_not_started_returns_correct_count(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();
        $book3 = Book::factory()->create();

        $user->books()->attach($book1, ['progression' => 0]);
        $user->books()->attach($book2, ['progression' => 0]);
        $user->books()->attach($book3, ['progression' => 50]);

        $notStarted = Book::BooksNotStarted($user->id);

        $this->assertEquals(2, $notStarted);
    }

    public function test_get_books_from_user_order_by_date_creation_desc(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['created_at' => now()->subDays(2)]);
        $book2 = Book::factory()->create(['created_at' => now()->subDay()]);
        $book3 = Book::factory()->create(['created_at' => now()]);

        $user->books()->attach($book1, ['progression' => 0]);
        $user->books()->attach($book2, ['progression' => 0]);
        $user->books()->attach($book3, ['progression' => 0]);

        $books = Book::getBooksFromUserOrderByDateCreationDesc($user->id);

        $this->assertCount(3, $books);
        $this->assertEquals($book3->id, $books[0]['id']);
        $this->assertEquals($book2->id, $books[1]['id']);
        $this->assertEquals($book1->id, $books[2]['id']);
    }

    public function test_get_list_of_authors_based_on_user_library(): void
    {
        $user = User::factory()->create();
        $author1 = Author::factory()->create();
        $author2 = Author::factory()->create();

        $book1 = Book::factory()->create(['author_id' => $author1->id]);
        $book2 = Book::factory()->create(['author_id' => $author2->id]);

        $user->books()->attach($book1, ['progression' => 0]);
        $user->books()->attach($book2, ['progression' => 0]);

        $authors = Book::getListOfAuthorsBasedOnUserLibrary($user->id);

        $this->assertCount(2, $authors);
    }

    public function test_filter_books_by_search(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['title' => 'Harry Potter and the Philosopher Stone']);
        $book2 = Book::factory()->create(['title' => 'The Lord of the Rings']);

        $user->books()->attach($book1, ['progression' => 0]);
        $user->books()->attach($book2, ['progression' => 0]);

        $result = Book::filterBooks($user->id, [], [], 'Harry', 10);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Harry Potter and the Philosopher Stone', $result->first()->title);
    }

    public function test_filter_books_by_author(): void
    {
        $user = User::factory()->create();
        $author1 = Author::factory()->create();
        $author2 = Author::factory()->create();

        $book1 = Book::factory()->create(['author_id' => $author1->id]);
        $book2 = Book::factory()->create(['author_id' => $author2->id]);

        $user->books()->attach($book1, ['progression' => 0]);
        $user->books()->attach($book2, ['progression' => 0]);

        $result = Book::filterBooks($user->id, [$author1->id], [], '', 10);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($book1->id, $result->first()->id);
    }

    public function test_get_books_not_finished_by_user_order_by_date_creation_desc(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create(['created_at' => now()->subDays(2)]);
        $book2 = Book::factory()->create(['created_at' => now()->subDay()]);
        $book3 = Book::factory()->create(['created_at' => now()]);

        $user->books()->attach($book1, ['progression' => 50]); // not finished
        $user->books()->attach($book2, ['progression' => 100]); // finished
        $user->books()->attach($book3, ['progression' => 30]); // not finished

        $books = Book::getBooksNotFinishedByUserOrderByDateCreationDesc($user->id);

        $this->assertCount(2, $books);
    }

    public function test_get_list_of_books_based_on_author(): void
    {
        $author = Author::factory()->create();
        $book1 = Book::factory()->create(['author_id' => $author->id]);
        $book2 = Book::factory()->create(['author_id' => $author->id]);

        $books = Book::getListOfBooksBasedOnAuthor($author->id);

        $this->assertCount(2, $books);
    }

    public function test_retrieve_progression_returns_correct_data(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();
        $user->books()->attach($book, ['progression' => 75]);

        $booksWithProgression = Book::RetrieveProgression(
            $book->where('id', $book->id)->with(['users' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])->get()
        );

        $this->assertArrayHasKey('progression', $booksWithProgression[0]);
        $this->assertEquals(75, $booksWithProgression[0]['progression']);
    }
}
