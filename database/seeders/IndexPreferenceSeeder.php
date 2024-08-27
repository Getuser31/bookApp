<?php

namespace Database\Seeders;

use App\Models\IndexPreference;
use Illuminate\Database\Seeder;

class IndexPreferenceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        IndexPreference::insert([
            ['name' => 'Latest Books Added', 'description' => 'Display the latest book you added in your library'],
            ['name' => 'Books not Finished', 'description' => "Display the book you didn't finished yet"],
            ['name' => 'Favorite Books', 'description' => 'Display your favorites books'],
            ['name' => 'Books rating', 'description' => 'Display the books you rated the most'],
            ['name' => 'Best Books overall', 'description' => 'Display the best books overall'],
            ]);
    }
}
