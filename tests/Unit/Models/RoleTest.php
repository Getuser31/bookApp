<?php

namespace Tests\Unit\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_can_be_created(): void
    {
        $role = Role::factory()->create([
            'name' => 'Admin',
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'name' => 'Admin',
        ]);
    }

    public function test_role_can_have_users(): void
    {
        $role = Role::factory()->create();
        $user = User::factory()->create(['role_id' => $role->id]);

        $this->assertTrue($role->users()->exists());
        $this->assertEquals(1, $role->users()->count());
    }

    public function test_role_has_fillable_attributes(): void
    {
        $role = new Role();

        $this->assertEquals(['name'], $role->getFillable());
    }

    public function test_get_user_role_returns_role_id(): void
    {
        // getUserRole looks for 'user' (lowercase)
        $role = Role::factory()->create(['name' => 'user']);

        $roleId = Role::getUserRole();

        $this->assertEquals($role->id, $roleId);
    }
}
