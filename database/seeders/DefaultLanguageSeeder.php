<?php

namespace Database\Seeders;

use App\Models\DefaultLanguage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DefaultLanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DefaultLanguage::insert([
            ['language' => 'en'],
            ['language' => 'fr'],
        ]);
    }
}
