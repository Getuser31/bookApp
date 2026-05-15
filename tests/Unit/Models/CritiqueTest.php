<?php

namespace Tests\Unit\Models;

use App\Models\Critique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CritiqueTest extends TestCase
{
    use RefreshDatabase;

    public function test_critique_can_be_created(): void
    {
        $critique = Critique::factory()->create([
            'critique' => 'An excellent critique of the book.',
        ]);

        $this->assertDatabaseHas('critiques', [
            'id' => $critique->id,
            'critique' => 'An excellent critique of the book.',
        ]);
    }
}
