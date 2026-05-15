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
}
