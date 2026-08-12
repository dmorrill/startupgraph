<?php

namespace Database\Factories;

use App\Models\CompanyList;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyListFactory extends Factory
{
    protected $model = CompanyList::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
        ];
    }
}
