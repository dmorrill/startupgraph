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
            'source' => fake()->randomElement(['wikipedia', 'yc', 'github-orgs', 'producthunt']),
            'companies_created' => fake()->numberBetween(0, 100),
            'companies_updated' => fake()->numberBetween(0, 50),
            'companies_skipped' => fake()->numberBetween(0, 20),
            'total_processed' => fake()->numberBetween(0, 200),
            'status' => 'completed',
            'started_at' => now()->subMinutes(5),
            'completed_at' => now(),
        ];
    }
}
