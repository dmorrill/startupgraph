<?php

namespace Database\Factories;

use App\Models\Person;
use Illuminate\Database\Eloquent\Factories\Factory;

class PersonFactory extends Factory
{
    protected $model = Person::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'slug' => fake()->unique()->slug(),
            'bio' => fake()->sentence(),
            'linkedin_url' => 'https://linkedin.com/in/'.fake()->slug(),
        ];
    }
}
