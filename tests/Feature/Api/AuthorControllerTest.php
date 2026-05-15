<?php

namespace Tests\Feature\Api;

use App\Models\Author;
use App\Models\Book;
use App\Models\DefaultLanguage;
use App\Models\IndexPreference;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuthorControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

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

        // Create a regular user
        $role = Role::where('name', 'User')->first();
        $this->user = User::factory()->create(['role_id' => $role->id]);

        UserPreference::create([
            'user_id' => $this->user->id,
            'default_language_id' => 1,
            'index_preference_id' => 1,
        ]);
    }

    /** @test */
    public function user_can_get_books_from_author()
    {
        Sanctum::actingAs($this->user);

        $author = Author::factory()->create();
        $book1 = Book::factory()->create(['author_id' => $author->id]);
        $book2 = Book::factory()->create(['author_id' => $author->id]);

        $response = $this->getJson("/api/getBookFromAuthor/{$author->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'books',
            ]);
    }

    /** @test */
    public function returns_empty_array_when_author_has_no_books()
    {
        Sanctum::actingAs($this->user);

        $author = Author::factory()->create();

        $response = $this->getJson("/api/getBookFromAuthor/{$author->id}");

        $response->assertStatus(200)
            ->assertJson([
                'books' => [],
            ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_author_books()
    {
        $response = $this->getJson('/api/getBookFromAuthor/1');

        $response->assertStatus(401);
    }
}
