<?php

namespace Database\Factories;

use App\Models\Critique;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Critique>
 */
class CritiqueFactory extends Factory
{
    protected $model = Critique::class;

    public function definition(): array
    {
        return [
            'critique' => fake()->paragraph(),
            'books_id' => \App\Models\Book::factory(),
        ];
    }
}
