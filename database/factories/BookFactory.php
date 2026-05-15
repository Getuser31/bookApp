<?php

namespace Database\Factories;

use App\Models\Author;
use App\Models\Book;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Book>
 */
class BookFactory extends Factory
{
    protected $model = Book::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(),
            'date_of_publication' => fake()->date('Y-m-d', 'now'),
            'author_id' => Author::factory(),
            'picture' => null,
            'google_id' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function withGoogleId(): static
    {
        return $this->state(fn(array $attributes) => [
            'google_id' => fake()->unique()->uuid(),
        ]);
    }
}
