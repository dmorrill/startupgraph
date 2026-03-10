<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\ScheduledTaskExecution;
use Illuminate\Database\Eloquent\Factories\Factory;

class ScheduledTaskExecutionFactory extends Factory
{
    protected $model = ScheduledTaskExecution::class;

    public function definition(): array
    {
        return [
            'task_type' => fake()->randomElement(['news_fetch', 'headcount_update', 'funding_check']),
            'company_id' => Company::factory(),
            'status' => 'success',
            'error_message' => null,
            'metadata' => ['source' => 'test'],
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
            'completed_at' => now(),
        ]);
    }

    public function running(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'running',
            'completed_at' => null,
        ]);
    }
}
