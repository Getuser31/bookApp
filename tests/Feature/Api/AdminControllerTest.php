<?php

namespace Tests\Feature\Api;

use App\Models\Author;
use App\Models\Book;
use App\Models\DefaultLanguage;
use App\Models\Genre;
use App\Models\IndexPreference;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $adminUser;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        $adminRole = Role::factory()->admin()->create();
        $userRole = Role::factory()->user()->create();

        // Create default languages
        DefaultLanguage::factory()->create(['language' => 'en']);
        DefaultLanguage::factory()->create(['language' => 'fr']);

        // Create index preferences
        IndexPreference::factory()->create(['name' => 'Latest Books Added']);

        // Create admin user
        $this->adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role_id' => $adminRole->id,
        ]);

        // Create regular user
        $this->regularUser = User::factory()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
            'role_id' => $userRole->id,
        ]);
    }

    // Genre management

    /** @test */
    public function admin_can_get_all_genres()
    {
        Sanctum::actingAs($this->adminUser);

        Genre::factory()->count(3)->create();

        $response = $this->getJson('/api/handleGenre');

        $response->assertStatus(200)
            ->assertJsonStructure(['genres']);
    }

    /** @test */
    public function non_admin_cannot_access_admin_routes()
    {
        Sanctum::actingAs($this->regularUser);

        $response = $this->getJson('/api/handleGenre');

        $response->assertStatus(302); // Redirected because middleware redirects non-admins
    }

    /** @test */
    public function admin_can_create_a_genre()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/addNewGenre', [
            'name' => 'Thriller',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Genre has been created']);

        $this->assertDatabaseHas('genres', [
            'name' => 'Thriller',
        ]);
    }

    /** @test */
    public function admin_can_update_a_genre()
    {
        Sanctum::actingAs($this->adminUser);

        $genre = Genre::factory()->create(['name' => 'Old Genre']);

        $response = $this->postJson("/api/updateGenre/{$genre->id}", [
            'name' => 'Updated Genre',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Genre updated']);

        $this->assertDatabaseHas('genres', [
            'id' => $genre->id,
            'name' => 'Updated Genre',
        ]);
    }

    /** @test */
    public function admin_can_delete_a_genre()
    {
        Sanctum::actingAs($this->adminUser);

        $genre = Genre::factory()->create();

        $response = $this->deleteJson("/api/deleteGenre/{$genre->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Genre has been deleted']);

        $this->assertDatabaseMissing('genres', ['id' => $genre->id]);
    }

    // Author management

    /** @test */
    public function admin_can_get_all_authors()
    {
        Sanctum::actingAs($this->adminUser);

        Author::factory()->count(3)->create();

        $response = $this->getJson('/api/handleAuthors');

        $response->assertStatus(200)
            ->assertJsonStructure(['authors']);
    }

    /** @test */
    public function admin_can_create_an_author()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/addNewAuthor', [
            'name' => 'Stephen King',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Author has been created']);

        $this->assertDatabaseHas('authors', [
            'name' => 'Stephen King',
        ]);
    }

    /** @test */
    public function admin_can_update_an_author()
    {
        Sanctum::actingAs($this->adminUser);

        $author = Author::factory()->create(['name' => 'Old Author']);

        $response = $this->postJson("/api/updateAuthor/{$author->id}", [
            'name' => 'Updated Author',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Author updated']);

        $this->assertDatabaseHas('authors', [
            'id' => $author->id,
            'name' => 'Updated Author',
        ]);
    }

    /** @test */
    public function admin_can_delete_an_author()
    {
        Sanctum::actingAs($this->adminUser);

        $author = Author::factory()->create();

        $response = $this->deleteJson("/api/deleteAuthor/{$author->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Author deleted']);

        $this->assertDatabaseMissing('authors', ['id' => $author->id]);
    }

    // Book management

    /** @test */
    public function admin_can_get_all_books()
    {
        Sanctum::actingAs($this->adminUser);

        Book::factory()->count(3)->create();

        $response = $this->getJson('/api/handleBooks');

        $response->assertStatus(200)
            ->assertJsonStructure(['books']);
    }

    /** @test */
    public function admin_can_delete_a_book()
    {
        Sanctum::actingAs($this->adminUser);

        $book = Book::factory()->create();

        $response = $this->deleteJson("/api/deleteBook/{$book->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Book deleted']);

        $this->assertDatabaseMissing('books', ['id' => $book->id]);
    }

    /** @test */
    public function delete_book_returns_404_when_not_found()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->deleteJson('/api/deleteBook/999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Book not found']);
    }

    // User management

    /** @test */
    public function admin_can_get_all_users()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->getJson('/api/handleUsers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'users', 'roles',
            ]);
    }

    /** @test */
    public function create_genre_fails_without_name()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/addNewGenre', [
            'name' => '',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function create_author_fails_without_name()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/addNewAuthor', [
            'name' => '',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function update_genre_fails_when_not_found()
    {
        Sanctum::actingAs($this->adminUser);

        $response = $this->postJson('/api/updateGenre/999', [
            'name' => 'New Name',
        ]);

        $response->assertStatus(404);
    }
}
