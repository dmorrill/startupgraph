<?php

namespace Database\Factories;

use App\Models\CompanyImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CompanyImport>
 */
class CompanyImportFactory extends Factory
{
    protected $model = CompanyImport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => $this->faker->randomElement(['crunchbase', 'wikipedia', 'github', 'manual']),
            'batch_id' => $this->faker->uuid(),
            'companies_created' => $this->faker->numberBetween(0, 100),
            'companies_updated' => $this->faker->numberBetween(0, 50),
            'companies_skipped' => $this->faker->numberBetween(0, 20),
            'total_processed' => function (array $attributes) {
                return $attributes['companies_created'] + $attributes['companies_updated'] + $attributes['companies_skipped'];
            },
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'last_page' => $this->faker->numberBetween(1, 100),
            'last_offset' => $this->faker->numberBetween(0, 1000),
            'metadata' => [
                'api_version' => '1.0',
                'user_agent' => $this->faker->userAgent(),
            ],
            'error_message' => null,
            'started_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'completed_at' => function (array $attributes) {
                return $attributes['status'] === 'completed' ? $this->faker->dateTimeBetween($attributes['started_at'], 'now') : null;
            },
        ];
    }

    /**
     * Indicate that the import failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
            'error_message' => $this->faker->sentence(),
            'completed_at' => $this->faker->dateTimeBetween($attributes['started_at'], 'now'),
        ]);
    }

    /**
     * Indicate that the import is still processing.
     */
    public function processing(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'processing',
            'completed_at' => null,
        ]);
    }
}