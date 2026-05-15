<?php

namespace Tests\Unit\Models;

use App\Models\DefaultLanguage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DefaultLanguageTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_language_can_be_created(): void
    {
        $language = DefaultLanguage::factory()->create([
            'language' => 'en',
        ]);

        $this->assertDatabaseHas('default_language', [
            'id' => $language->id,
            'language' => 'en',
        ]);
    }

    public function test_default_language_has_fillable_attributes(): void
    {
        $language = new DefaultLanguage();

        $this->assertEquals(['language'], $language->getFillable());
    }

    public function test_default_language_has_correct_table(): void
    {
        $language = new DefaultLanguage();

        $this->assertEquals('default_language', $language->getTable());
    }
}
