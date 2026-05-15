<?php

namespace Tests\Feature\Api;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookRating;
use App\Models\DefaultLanguage;
use App\Models\Genre;
use App\Models\IndexPreference;
use App\Models\Notes;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BookControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Author $author;
    private Genre $genre1;
    private Genre $genre2;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::factory()->admin()->create();
        Role::factory()->user()->create();

        // Create default languages
        DefaultLanguage::factory()->create(['language' => 'en']);
        DefaultLanguage::factory()->create(['language' => 'fr']);

        // Create index preferences
        IndexPreference::factory()->create(['name' => 'Latest Books Added']);
        IndexPreference::factory()->create(['name' => 'Books not Finished']);
        IndexPreference::factory()->create(['name' => 'Favorite Books']);
        IndexPreference::factory()->create(['name' => 'Books rating']);

        // Create a regular user
        $role = Role::where('name', 'User')->first();
        $this->user = User::factory()->create(['role_id' => $role->id]);

        // Create user preference
        UserPreference::create([
            'user_id' => $this->user->id,
            'default_language_id' => 1,
            'index_preference_id' => 1,
        ]);

        // Create author and genres
        $this->author = Author::factory()->create(['name' => 'Test Author']);
        $this->genre1 = Genre::factory()->create(['name' => 'Action']);
        $this->genre2 = Genre::factory()->create(['name' => 'Romance']);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_books()
    {
        $response = $this->getJson('/api/index');

        $response->assertStatus(401);
    }

    /** @test */
    public function authenticated_user_can_get_their_books()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $book->genres()->attach([$this->genre1->id]);
        $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);

        $response = $this->getJson('/api/index');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'books',
            ]);
    }

    /** @test */
    public function authenticated_user_can_view_book_details()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $book->genres()->attach([$this->genre1->id]);
        $this->user->books()->attach($book, ['progression' => 50, 'favorite' => true]);

        $response = $this->getJson("/api/book/{$book->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'book', 'progression', 'belongToUser', 'rating', 'favorite', 'notes',
            ])
            ->assertJson([
                'belongToUser' => true,
                'favorite' => true,
            ]);
    }

    /** @test */
    public function user_can_search_google_books()
    {
        Sanctum::actingAs($this->user);

        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(['items' => []]);

        $response = $this->getJson('/api/searchGoogleBooks?title=Harry+Potter');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_filter_library()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create([
            'title' => 'Test Book',
            'author_id' => $this->author->id,
        ]);
        $book->genres()->attach([$this->genre1->id]);
        $this->user->books()->attach($book, ['progression' => 0]);

        $response = $this->postJson('/api/filterLibrary', [
            'authors' => (string)$this->author->id,
            'genres' => (string)$this->genre1->id,
            'search' => 'Test',
            'per_page' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'books', 'total', 'current_page', 'per_page', 'last_page',
            ]);
    }

    /** @test */
    public function user_can_update_rating()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);

        $response = $this->postJson('/api/updateRating', [
            'bookId' => $book->id,
            'rating' => 8,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Rating updated successfully']);

        $this->assertDatabaseHas('book_rating', [
            'book_id' => $book->id,
            'user_id' => $this->user->id,
            'rating' => 8,
        ]);
    }

    /** @test */
    public function update_rating_fails_with_invalid_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/updateRating', [
            'bookId' => 999,
            'rating' => 20,
        ]);

        $response->assertStatus(400);
    }

    /** @test */
    public function user_can_update_favorite()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);

        $response = $this->postJson('/api/updateFavorite', [
            'bookId' => $book->id,
            'favorite' => 'true',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function user_can_update_progression()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);

        $response = $this->postJson('/api/updateProgression', [
            'bookId' => $book->id,
            'progression' => 50,
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Progression updated successfully']);
    }

    /** @test */
    public function update_progression_with_100_marks_completed_at()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);

        $response = $this->postJson('/api/updateProgression', [
            'bookId' => $book->id,
            'progression' => 100,
        ]);

        $response->assertStatus(200);
        $this->assertNotNull(
            $this->user->books()->where('book_id', $book->id)->first()->pivot->completed_at
        );
    }

    /** @test */
    public function user_can_store_a_note()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);

        $response = $this->postJson('/api/storeNote', [
            'content' => 'This is a great note!',
            'bookId' => $book->id,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('notes', [
            'content' => 'This is a great note!',
            'user_id' => $this->user->id,
            'book_id' => $book->id,
        ]);
    }

    /** @test */
    public function user_can_get_library()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $book->genres()->attach([$this->genre1->id]);
        $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);

        $response = $this->getJson('/api/library');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'books', 'genres', 'authors',
            ]);
    }

    /** @test */
    public function user_can_get_genres()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/getGenres');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'genres',
            ]);
    }

    /** @test */
    public function user_can_get_authors()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/getAuthors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'authors',
            ]);
    }

    /** @test */
    public function user_can_store_an_author()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/storeAuthor', [
            'author' => 'New Author',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('authors', [
            'name' => 'New Author',
        ]);
    }

    /** @test */
    public function user_can_store_a_book()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/storeBook', [
            'title' => 'New Book',
            'description' => 'A great book description',
            'date_of_publication' => '15/01/2024',
            'author_id' => $this->author->id,
            'genres' => [$this->genre1->id, $this->genre2->id],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function store_book_fails_with_invalid_data()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/storeBook', [
            'title' => '',
            'description' => '',
            'author_id' => 999,
            'genres' => [],
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_can_update_a_book()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create([
            'author_id' => $this->author->id,
            'user_id' => $this->user->id,
        ]);
        $book->genres()->attach([$this->genre1->id]);
        $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);

        $response = $this->postJson("/api/updateBook/{$book->id}", [
            'title' => 'Updated Book Title',
            'description' => 'Updated description',
            'date_of_publication' => '15/01/2024',
            'author_id' => $this->author->id,
            'genres' => [$this->genre1->id],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function user_can_remove_a_book()
    {
        Sanctum::actingAs($this->user);

        $book = Book::factory()->create(['author_id' => $this->author->id]);
        $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);

        $response = $this->deleteJson("/api/removeBook/{$book->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('book_user', [
            'user_id' => $this->user->id,
            'book_id' => $book->id,
        ]);
    }

    // ------------------------------
    // Sort Functionality Tests
    // ------------------------------

    /** @test */
    public function filter_library_sorts_by_title_ascending()
    {
        Sanctum::actingAs($this->user);

        $bookA = Book::factory()->create([
            'title' => 'A Great Book',
            'author_id' => $this->author->id,
        ]);
        $bookB = Book::factory()->create([
            'title' => 'Best Book Ever',
            'author_id' => $this->author->id,
        ]);
        $bookC = Book::factory()->create([
            'title' => 'Z Any Book',
            'author_id' => $this->author->id,
        ]);

        foreach ([$bookA, $bookB, $bookC] as $book) {
            $book->genres()->attach([$this->genre1->id]);
            $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);
        }

        $response = $this->postJson('/api/filterLibrary', [
            'sort_by' => 'title',
            'sort_dir' => 'asc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200);
        $books = $response->json('books');
        $this->assertEquals('A Great Book', $books[0]['title']);
        $this->assertEquals('Best Book Ever', $books[1]['title']);
        $this->assertEquals('Z Any Book', $books[2]['title']);
    }

    /** @test */
    public function filter_library_sorts_by_title_descending()
    {
        Sanctum::actingAs($this->user);

        $bookA = Book::factory()->create([
            'title' => 'A Great Book',
            'author_id' => $this->author->id,
        ]);
        $bookB = Book::factory()->create([
            'title' => 'Best Book Ever',
            'author_id' => $this->author->id,
        ]);
        $bookC = Book::factory()->create([
            'title' => 'Z Any Book',
            'author_id' => $this->author->id,
        ]);

        foreach ([$bookA, $bookB, $bookC] as $book) {
            $book->genres()->attach([$this->genre1->id]);
            $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);
        }

        $response = $this->postJson('/api/filterLibrary', [
            'sort_by' => 'title',
            'sort_dir' => 'desc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200);
        $books = $response->json('books');
        $this->assertEquals('Z Any Book', $books[0]['title']);
        $this->assertEquals('Best Book Ever', $books[1]['title']);
        $this->assertEquals('A Great Book', $books[2]['title']);
    }

    /** @test */
    public function filter_library_sorts_by_author_name_ascending()
    {
        Sanctum::actingAs($this->user);

        $authorA = Author::factory()->create(['name' => 'Adam Smith']);
        $authorB = Author::factory()->create(['name' => 'Bob Jones']);
        $authorC = Author::factory()->create(['name' => 'Charlie Brown']);

        $bookA = Book::factory()->create(['title' => 'Book A', 'author_id' => $authorA->id]);
        $bookB = Book::factory()->create(['title' => 'Book B', 'author_id' => $authorB->id]);
        $bookC = Book::factory()->create(['title' => 'Book C', 'author_id' => $authorC->id]);

        foreach ([$bookA, $bookB, $bookC] as $book) {
            $book->genres()->attach([$this->genre1->id]);
            $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);
        }

        $response = $this->postJson('/api/filterLibrary', [
            'sort_by' => 'author',
            'sort_dir' => 'asc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200);
        $books = $response->json('books');
        $this->assertEquals('Adam Smith', $books[0]['author']['name']);
        $this->assertEquals('Bob Jones', $books[1]['author']['name']);
        $this->assertEquals('Charlie Brown', $books[2]['author']['name']);
    }

    /** @test */
    public function filter_library_sorts_by_author_name_descending()
    {
        Sanctum::actingAs($this->user);

        $authorA = Author::factory()->create(['name' => 'Adam Smith']);
        $authorB = Author::factory()->create(['name' => 'Bob Jones']);
        $authorC = Author::factory()->create(['name' => 'Charlie Brown']);

        $bookA = Book::factory()->create(['title' => 'Book A', 'author_id' => $authorA->id]);
        $bookB = Book::factory()->create(['title' => 'Book B', 'author_id' => $authorB->id]);
        $bookC = Book::factory()->create(['title' => 'Book C', 'author_id' => $authorC->id]);

        foreach ([$bookA, $bookB, $bookC] as $book) {
            $book->genres()->attach([$this->genre1->id]);
            $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);
        }

        $response = $this->postJson('/api/filterLibrary', [
            'sort_by' => 'author',
            'sort_dir' => 'desc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200);
        $books = $response->json('books');
        $this->assertEquals('Charlie Brown', $books[0]['author']['name']);
        $this->assertEquals('Bob Jones', $books[1]['author']['name']);
        $this->assertEquals('Adam Smith', $books[2]['author']['name']);
    }

    /** @test */
    public function filter_library_sorts_by_rating_ascending()
    {
        Sanctum::actingAs($this->user);

        $bookA = Book::factory()->create(['title' => 'Book A', 'author_id' => $this->author->id]);
        $bookB = Book::factory()->create(['title' => 'Book B', 'author_id' => $this->author->id]);
        $bookC = Book::factory()->create(['title' => 'Book C', 'author_id' => $this->author->id]);

        foreach ([$bookA, $bookB, $bookC] as $book) {
            $book->genres()->attach([$this->genre1->id]);
            $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);
        }

        // Set ratings
        BookRating::create(['book_id' => $bookA->id, 'user_id' => $this->user->id, 'rating' => 3]);
        BookRating::create(['book_id' => $bookB->id, 'user_id' => $this->user->id, 'rating' => 7]);
        BookRating::create(['book_id' => $bookC->id, 'user_id' => $this->user->id, 'rating' => 5]);

        $response = $this->postJson('/api/filterLibrary', [
            'sort_by' => 'rating',
            'sort_dir' => 'asc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200);
        $books = $response->json('books');

        // Filter out books that have no rating (null values) - ratings is a relation array
        $ratedBooks = array_values(array_filter($books, fn($b) => !empty($b['ratings'])));

        $this->assertCount(3, $ratedBooks);
        $this->assertEquals(3, $ratedBooks[0]['ratings'][0]['rating']);
        $this->assertEquals(5, $ratedBooks[1]['ratings'][0]['rating']);
        $this->assertEquals(7, $ratedBooks[2]['ratings'][0]['rating']);
    }

    /** @test */
    public function filter_library_sorts_by_rating_descending()
    {
        Sanctum::actingAs($this->user);

        $bookA = Book::factory()->create(['title' => 'Book A', 'author_id' => $this->author->id]);
        $bookB = Book::factory()->create(['title' => 'Book B', 'author_id' => $this->author->id]);
        $bookC = Book::factory()->create(['title' => 'Book C', 'author_id' => $this->author->id]);

        foreach ([$bookA, $bookB, $bookC] as $book) {
            $book->genres()->attach([$this->genre1->id]);
            $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);
        }

        // Set ratings
        BookRating::create(['book_id' => $bookA->id, 'user_id' => $this->user->id, 'rating' => 3]);
        BookRating::create(['book_id' => $bookB->id, 'user_id' => $this->user->id, 'rating' => 7]);
        BookRating::create(['book_id' => $bookC->id, 'user_id' => $this->user->id, 'rating' => 5]);

        $response = $this->postJson('/api/filterLibrary', [
            'sort_by' => 'rating',
            'sort_dir' => 'desc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200);
        $books = $response->json('books');

        // Filter out books that have no rating (null values) - ratings is a relation array
        $ratedBooks = array_values(array_filter($books, fn($b) => !empty($b['ratings'])));

        $this->assertCount(3, $ratedBooks);
        $this->assertEquals(7, $ratedBooks[0]['ratings'][0]['rating']);
        $this->assertEquals(5, $ratedBooks[1]['ratings'][0]['rating']);
        $this->assertEquals(3, $ratedBooks[2]['ratings'][0]['rating']);
    }

    /** @test */
    public function filter_library_sorts_by_progress_ascending()
    {
        Sanctum::actingAs($this->user);

        $bookA = Book::factory()->create(['title' => 'Book A', 'author_id' => $this->author->id]);
        $bookB = Book::factory()->create(['title' => 'Book B', 'author_id' => $this->author->id]);
        $bookC = Book::factory()->create(['title' => 'Book C', 'author_id' => $this->author->id]);

        foreach ([$bookA, $bookB, $bookC] as $book) {
            $book->genres()->attach([$this->genre1->id]);
        }

        $this->user->books()->attach($bookA, ['progression' => 10]);
        $this->user->books()->attach($bookB, ['progression' => 100]);
        $this->user->books()->attach($bookC, ['progression' => 50]);

        $response = $this->postJson('/api/filterLibrary', [
            'sort_by' => 'progress',
            'sort_dir' => 'asc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200);
        $books = $response->json('books');

        $this->assertEquals(10, $books[0]['pivot']['progression']);
        $this->assertEquals(50, $books[1]['pivot']['progression']);
        $this->assertEquals(100, $books[2]['pivot']['progression']);
    }

    /** @test */
    public function filter_library_sorts_by_progress_descending()
    {
        Sanctum::actingAs($this->user);

        $bookA = Book::factory()->create(['title' => 'Book A', 'author_id' => $this->author->id]);
        $bookB = Book::factory()->create(['title' => 'Book B', 'author_id' => $this->author->id]);
        $bookC = Book::factory()->create(['title' => 'Book C', 'author_id' => $this->author->id]);

        foreach ([$bookA, $bookB, $bookC] as $book) {
            $book->genres()->attach([$this->genre1->id]);
        }

        $this->user->books()->attach($bookA, ['progression' => 10]);
        $this->user->books()->attach($bookB, ['progression' => 100]);
        $this->user->books()->attach($bookC, ['progression' => 50]);

        $response = $this->postJson('/api/filterLibrary', [
            'sort_by' => 'progress',
            'sort_dir' => 'desc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200);
        $books = $response->json('books');

        $this->assertEquals(100, $books[0]['pivot']['progression']);
        $this->assertEquals(50, $books[1]['pivot']['progression']);
        $this->assertEquals(10, $books[2]['pivot']['progression']);
    }

    /** @test */
    public function filter_library_defaults_to_title_asc_when_invalid_sort_by()
    {
        Sanctum::actingAs($this->user);

        $bookB = Book::factory()->create([
            'title' => 'Beta Book',
            'author_id' => $this->author->id,
        ]);
        $bookA = Book::factory()->create([
            'title' => 'Alpha Book',
            'author_id' => $this->author->id,
        ]);

        foreach ([$bookA, $bookB] as $book) {
            $book->genres()->attach([$this->genre1->id]);
            $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);
        }

        $response = $this->postJson('/api/filterLibrary', [
            'sort_by' => 'invalid_field',
            'sort_dir' => 'asc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200);
        $books = $response->json('books');
        // Should default to title asc when sort_by is invalid
        $this->assertEquals('Alpha Book', $books[0]['title']);
        $this->assertEquals('Beta Book', $books[1]['title']);
    }

    /** @test */
    public function filter_library_preserves_sort_dir_when_sort_by_is_invalid()
    {
        Sanctum::actingAs($this->user);

        $bookB = Book::factory()->create([
            'title' => 'Beta Book',
            'author_id' => $this->author->id,
        ]);
        $bookA = Book::factory()->create([
            'title' => 'Alpha Book',
            'author_id' => $this->author->id,
        ]);

        foreach ([$bookA, $bookB] as $book) {
            $book->genres()->attach([$this->genre1->id]);
            $this->user->books()->attach($book, ['progression' => 0, 'favorite' => false]);
        }

        $response = $this->postJson('/api/filterLibrary', [
            'sort_by' => 'invalid_field',
            'sort_dir' => 'desc',
            'per_page' => 10,
        ]);

        $response->assertStatus(200);
        $books = $response->json('books');
        // Should default to title with desc direction from the explicit sort_dir
        $this->assertEquals('Beta Book', $books[0]['title']);
        $this->assertEquals('Alpha Book', $books[1]['title']);
    }
}
