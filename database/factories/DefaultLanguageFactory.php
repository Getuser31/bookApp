<?php

namespace Database\Factories;

use App\Models\DefaultLanguage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DefaultLanguage>
 */
class DefaultLanguageFactory extends Factory
{
    protected $model = DefaultLanguage::class;

    public function definition(): array
    {
        return [
            'language' => fake()->randomElement(['en', 'fr', 'nl', 'de']),
        ];
    }
}
