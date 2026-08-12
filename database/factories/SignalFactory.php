<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Signal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SignalFactory extends Factory
{
    protected $model = Signal::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'type' => Signal::TYPE_CUSTOM,
            'title' => fake()->sentence(5),
        ];
    }
}
