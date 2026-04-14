<?php

namespace Database\Factories;

use App\Models\CompanyImport;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanyImportFactory extends Factory
{
    protected $model = CompanyImport::class;

    public function definition(): array
    {
        return [
            'source' => $this->faker->randomElement(['wikipedia', 'crunchbase', 'manual', 'api']),
            'batch_id' => $this->faker->uuid,
            'companies_created' => $this->faker->numberBetween(0, 100),
            'companies_updated' => $this->faker->numberBetween(0, 50),
            'companies_skipped' => $this->faker->numberBetween(0, 10),
            'total_processed' => $this->faker->numberBetween(1, 200),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'last_page' => $this->faker->numberBetween(1, 10),
            'last_offset' => $this->faker->numberBetween(0, 1000),
            'metadata' => [],
            'error_message' => null,
            'started_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'completed_at' => $this->faker->optional(0.8)->dateTimeBetween('-1 week', 'now'),
        ];
    }

    public function completed(): static
    {
        return $this->state([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state([
            'status' => 'failed',
            'error_message' => 'Import failed due to API rate limit',
        ]);
    }
}