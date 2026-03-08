<?php

namespace Database\Factories;

use App\Models\SavedSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SavedSearchFactory extends Factory
{
    protected $model = SavedSearch::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'query' => fake()->word(),
            'filters_json' => ['category' => 'ai_ml', 'country' => 'US'],
            'notify_on_new' => fake()->boolean(),
            'last_result_count' => fake()->numberBetween(0, 100),
        ];
    }
}
