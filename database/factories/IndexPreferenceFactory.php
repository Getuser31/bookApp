<?php

namespace Database\Factories;

use App\Models\IndexPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<IndexPreference>
 */
class IndexPreferenceFactory extends Factory
{
    protected $model = IndexPreference::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word(),
            'description' => fake()->sentence(),
        ];
    }
}
