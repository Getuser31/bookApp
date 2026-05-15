<?php

namespace Tests\Unit\Models;

use App\Models\IndexPreference;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IndexPreferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_preference_can_be_created(): void
    {
        $preference = IndexPreference::factory()->create([
            'name' => 'Latest Books Added',
            'description' => 'Display the latest books added',
        ]);

        $this->assertDatabaseHas('index_preferences', [
            'id' => $preference->id,
            'name' => 'Latest Books Added',
        ]);
    }

    public function test_index_preference_has_fillable_attributes(): void
    {
        $preference = new IndexPreference();

        $this->assertEquals(['name', 'description'], $preference->getFillable());
    }
}
