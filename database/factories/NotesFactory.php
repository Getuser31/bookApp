<?php

namespace Database\Factories;

use App\Models\Book;
use App\Models\Notes;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Notes>
 */
class NotesFactory extends Factory
{
    protected $model = Notes::class;

    public function definition(): array
    {
        return [
            'content' => fake()->paragraph(),
            'user_id' => User::factory(),
            'book_id' => Book::factory(),
        ];
    }
}
