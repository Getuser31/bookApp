<?php

namespace Tests\Unit\Models;

use App\Models\DefaultLanguage;
use App\Models\IndexPreference;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_preference_can_be_created(): void
    {
        $user = User::factory()->create();
        $language = DefaultLanguage::factory()->create();
        $indexPreference = IndexPreference::factory()->create();

        $preference = UserPreference::create([
            'user_id' => $user->id,
            'default_language_id' => $language->id,
            'index_preference_id' => $indexPreference->id,
        ]);

        $this->assertDatabaseHas('user_preferences', [
            'id' => $preference->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_user_preference_has_default_attributes(): void
    {
        $preference = new UserPreference();

        $this->assertEquals([
            'default_language_id' => 1,
            'index_preference_id' => 1,
        ], $preference->getAttributes());
    }

    public function test_get_user_preference_returns_preference(): void
    {
        $user = User::factory()->create();
        $language = DefaultLanguage::factory()->create();
        $indexPreference = IndexPreference::factory()->create();

        UserPreference::create([
            'user_id' => $user->id,
            'default_language_id' => $language->id,
            'index_preference_id' => $indexPreference->id,
        ]);

        $preference = UserPreference::getUserPreference($user->id);

        $this->assertNotNull($preference);
        $this->assertEquals($user->id, $preference->user_id);
    }

    public function test_get_user_preference_returns_null_when_not_found(): void
    {
        $preference = UserPreference::getUserPreference(999);

        $this->assertNull($preference);
    }

    public function test_user_preference_belongs_to_default_language(): void
    {
        $language = DefaultLanguage::factory()->create(['language' => 'fr']);
        $preference = UserPreference::factory()->create([
            'default_language_id' => $language->id,
        ]);

        $this->assertInstanceOf(DefaultLanguage::class, $preference->defaultLanguage);
        $this->assertEquals($language->id, $preference->defaultLanguage->id);
    }
}
