<?php

namespace Database\Factories;

use App\Models\Investor;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvestorFactory extends Factory
{
    protected $model = Investor::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company() . ' Ventures',
            'slug' => fake()->unique()->slug(),
            'type' => fake()->randomElement(['vc', 'angel', 'corporate', 'accelerator', 'pe']),
            'website' => fake()->url(),
            'description' => fake()->sentence(),
            'portfolio_count' => fake()->numberBetween(5, 200),
        ];
    }
}
