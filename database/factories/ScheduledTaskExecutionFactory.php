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
        $started = fake()->dateTimeBetween('-7 days', 'now');

        return [
            'task_type' => fake()->randomElement(['headcount_update', 'funding_check', 'news_scan', 'oss_sync']),
            'company_id' => Company::factory(),
            'status' => fake()->randomElement(['pending', 'running', 'success', 'failed']),
            'error_message' => null,
            'metadata' => ['source' => 'test'],
            'started_at' => $started,
            'completed_at' => fake()->dateTimeBetween($started, 'now'),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => 'failed',
            'error_message' => fake()->sentence(),
        ]);
    }

    public function successful(): static
    {
        return $this->state(fn () => [
            'status' => 'success',
            'error_message' => null,
        ]);
    }
}
