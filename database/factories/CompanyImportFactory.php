<?php

namespace Database\Factories;

use App\Models\CompanyImport;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyImport>
 */
class CompanyImportFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => $this->faker->randomElement(['crunchbase', 'techcrunch', 'product_hunt']),
            'batch_id' => $this->faker->uuid(),
            'companies_created' => $this->faker->numberBetween(0, 100),
            'companies_updated' => $this->faker->numberBetween(0, 50),
            'companies_skipped' => $this->faker->numberBetween(0, 10),
            'total_processed' => $this->faker->numberBetween(50, 200),
            'status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'failed']),
            'last_page' => $this->faker->numberBetween(1, 10),
            'last_offset' => $this->faker->numberBetween(0, 1000),
            'metadata' => [
                'total_pages' => $this->faker->numberBetween(1, 20),
                'rate_limit' => $this->faker->boolean(),
            ],
            'error_message' => $this->faker->optional()->sentence(),
            'started_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'completed_at' => $this->faker->optional()->dateTimeBetween('-1 week', 'now'),
        ];
    }
}
