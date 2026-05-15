<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\BookRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookRatingTest extends TestCase
{
    use RefreshDatabase;

    public function test_book_rating_can_be_created(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $rating = BookRating::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 8,
        ]);

        $this->assertDatabaseHas('book_rating', [
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 8,
        ]);
    }

    public function test_book_rating_belongs_to_book(): void
    {
        $rating = BookRating::factory()->create();

        $this->assertInstanceOf(Book::class, $rating->book);
    }

    public function test_book_rating_belongs_to_user(): void
    {
        $rating = BookRating::factory()->create();

        $this->assertInstanceOf(User::class, $rating->user);
    }

    public function test_get_rating_returns_correct_rating(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        BookRating::create([
            'book_id' => $book->id,
            'user_id' => $user->id,
            'rating' => 7,
        ]);

        $rating = BookRating::getRating($book->id, $user->id);

        $this->assertNotNull($rating);
        $this->assertEquals(7, $rating->rating);
    }

    public function test_get_rating_returns_null_when_no_rating(): void
    {
        $rating = BookRating::getRating(999, 999);

        $this->assertNull($rating);
    }

    public function test_get_average_book_rating(): void
    {
        $user = User::factory()->create();
        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        BookRating::create(['book_id' => $book1->id, 'user_id' => $user->id, 'rating' => 6]);
        BookRating::create(['book_id' => $book2->id, 'user_id' => $user->id, 'rating' => 10]);

        $average = BookRating::getAverageBookRating($user->id);

        $this->assertEquals(8.0, $average);
    }

    public function test_get_average_book_rating_returns_zero_when_no_ratings(): void
    {
        $user = User::factory()->create();

        $average = BookRating::getAverageBookRating($user->id);

        $this->assertEquals(0, $average);
    }

    public function test_rating_must_be_between_one_and_ten(): void
    {
        $rating = BookRating::factory()->create(['rating' => 5]);

        $this->assertGreaterThanOrEqual(1, $rating->rating);
        $this->assertLessThanOrEqual(10, $rating->rating);
    }
}
