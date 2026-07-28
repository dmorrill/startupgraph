<?php

namespace Database\Factories;

use App\Models\Screen;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScreenFactory extends Factory
{
    protected $model = Screen::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(3, true),
            'criteria' => ['category' => 'ai_ml'],
        ];
    }
}
