<?php

namespace Database\Factories;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class FeedbackFactory extends Factory
{
    protected $model = Feedback::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'page_url' => $this->faker->url,
            'message' => $this->faker->paragraph,
        ];
    }

    public function anonymous(): static
    {
        return $this->state(['user_id' => null]);
    }
}