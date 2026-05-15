<?php

namespace Database\Factories;

use App\Models\DefaultLanguage;
use App\Models\IndexPreference;
use App\Models\User;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserPreference>
 */
class UserPreferenceFactory extends Factory
{
    protected $model = UserPreference::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'default_language_id' => DefaultLanguage::factory(),
            'index_preference_id' => IndexPreference::factory(),
        ];
    }
}
