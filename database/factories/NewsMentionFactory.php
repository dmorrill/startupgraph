<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\NewsMention;
use Illuminate\Database\Eloquent\Factories\Factory;

class NewsMentionFactory extends Factory
{
    protected $model = NewsMention::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'title' => fake()->sentence(),
            'url' => fake()->url(),
            'source' => fake()->randomElement(['techcrunch', 'bloomberg', 'reuters']),
            'published_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'summary' => fake()->paragraph(),
        ];
    }
}
