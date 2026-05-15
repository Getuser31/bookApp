<?php

namespace Tests\Unit\Models;

use App\Models\Book;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }

    public function test_user_has_fillable_attributes(): void
    {
        $user = new User();

        $this->assertEquals(['name', 'email', 'password', 'role_id'], $user->getFillable());
    }

    public function test_user_password_is_hashed(): void
    {
        $user = User::factory()->create([
            'password' => 'plain-password',
        ]);

        $this->assertNotEquals('plain-password', $user->password);
        $this->assertTrue(Hash::check('plain-password', $user->password));
    }

    public function test_user_belongs_to_many_books(): void
    {
        $user = User::factory()->create();
        $book = Book::factory()->create();

        $user->books()->attach($book, ['progression' => 0, 'favorite' => false]);

        $this->assertTrue($user->books()->exists());
        $this->assertEquals(1, $user->books()->count());
    }

    public function test_user_belongs_to_role(): void
    {
        $role = Role::factory()->create(['name' => 'Admin']);
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertInstanceOf(Role::class, $user->role);
        $this->assertEquals($role->id, $user->role->id);
    }

    public function test_check_admin_returns_true_for_admin(): void
    {
        $role = Role::factory()->admin()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($user->checkAdmin());
    }

    public function test_check_admin_returns_false_for_non_admin(): void
    {
        // Create both Admin and User roles so checkAdmin() can look up Admin role id
        Role::factory()->admin()->create();
        $userRole = Role::factory()->user()->create();
        $user = User::factory()->create(['role_id' => $userRole->id]);

        $this->assertFalse($user->checkAdmin());
    }

    public function test_find_by_username(): void
    {
        $user = User::factory()->create(['name' => 'UniqueUser']);

        $found = (new User())->findByUsername('UniqueUser');

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_by_username_returns_null_when_not_found(): void
    {
        $found = (new User())->findByUsername('NonExistentUser');

        $this->assertNull($found);
    }

    public function test_find_by_email(): void
    {
        $user = User::factory()->create(['email' => 'unique@example.com']);

        $found = (new User())->findByEmail('unique@example.com');

        $this->assertNotNull($found);
        $this->assertEquals($user->id, $found->id);
    }

    public function test_find_by_email_returns_null_when_not_found(): void
    {
        $found = (new User())->findByEmail('nonexistent@example.com');

        $this->assertNull($found);
    }

    public function test_user_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }

    public function test_user_has_hidden_password(): void
    {
        $user = User::factory()->create();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }
}
