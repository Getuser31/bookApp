<?php

namespace Tests\Feature\Api;

use App\Models\Book;
use App\Models\BookRating;
use App\Models\DefaultLanguage;
use App\Models\IndexPreference;
use App\Models\Role;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

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
    }

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
            'role_id' => $role->id,
        ]);

        // Create user preference so login works
        UserPreference::create([
            'user_id' => $user->id,
            'default_language_id' => 1,
            'index_preference_id' => 1,
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success', 'user', 'token',
            ])
            ->assertJson(['success' => 'login successful']);
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => 'Wrong username or password']);
    }

    /** @test */
    public function user_can_register()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'NewUser',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success', 'user', 'token',
            ])
            ->assertJson(['success' => true]);

        $userRole = Role::where('name', 'User')->first();
        $this->assertDatabaseHas('users', [
            'name' => 'NewUser',
            'email' => 'newuser@example.com',
            'role_id' => $userRole->id,
        ]);
    }

    /** @test */
    public function registration_fails_with_existing_email()
    {
        $role = Role::where('name', 'User')->first();
        User::factory()->create([
            'email' => 'existing@example.com',
            'role_id' => $role->id,
        ]);

        $response = $this->postJson('/api/register', [
            'name' => 'AnotherUser',
            'email' => 'existing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function authenticated_user_can_view_profile()
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create(['role_id' => $role->id]);
        UserPreference::create([
            'user_id' => $user->id,
            'default_language_id' => 1,
            'index_preference_id' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/userProfile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user', 'averageRanking', 'bookStarted', 'bookNotStarted',
                'indexPreferences', 'userPreferences', 'defaultLanguages', 'booksFinished',
            ]);
    }

    /** @test */
    public function authenticated_user_can_update_index_preference()
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create(['role_id' => $role->id]);
        UserPreference::create([
            'user_id' => $user->id,
            'default_language_id' => 1,
            'index_preference_id' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/updateIndexPreference', [
            'index_preference_id' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function authenticated_user_can_update_language_preference()
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create(['role_id' => $role->id]);
        UserPreference::create([
            'user_id' => $user->id,
            'default_language_id' => 1,
            'index_preference_id' => 1,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/updateLanguage', [
            'default_language_id' => 2,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function authenticated_user_can_update_their_data()
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create([
            'name' => 'OriginalName',
            'email' => 'original@example.com',
            'role_id' => $role->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/updateUserData', [
            'name' => 'UpdatedName',
            'email' => 'updated@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'UpdatedName',
            'email' => 'updated@example.com',
        ]);
    }

    /** @test */
    public function authenticated_user_can_update_password()
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
            'role_id' => $role->id,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/updatePassword', [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_profile()
    {
        $response = $this->getJson('/api/userProfile');

        $response->assertStatus(401);
    }

    /** @test */
    public function user_profile_contains_book_statistics()
    {
        $role = Role::where('name', 'User')->first();
        $user = User::factory()->create(['role_id' => $role->id]);
        UserPreference::create([
            'user_id' => $user->id,
            'default_language_id' => 1,
            'index_preference_id' => 1,
        ]);

        $book1 = Book::factory()->create();
        $book2 = Book::factory()->create();

        $user->books()->attach($book1, ['progression' => 50, 'favorite' => false]);
        $user->books()->attach($book2, ['progression' => 100, 'favorite' => true]);

        BookRating::create([
            'book_id' => $book1->id,
            'user_id' => $user->id,
            'rating' => 8,
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/userProfile');

        $response->assertStatus(200)
            ->assertJson([
                'bookStarted' => 1,
                'booksFinished' => 1,
                'bookNotStarted' => 0,
            ]);
    }
}
