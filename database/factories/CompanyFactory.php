<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'website' => fake()->url(),
            'description' => fake()->sentence(),
            'city' => fake()->city(),
            'country' => 'US',
            'category' => fake()->randomElement(array_keys(Company::CATEGORIES)),
            'founded_date' => fake()->dateTimeBetween('-10 years', '-1 year'),
            'current_headcount' => fake()->numberBetween(10, 5000),
        ];
    }
}
