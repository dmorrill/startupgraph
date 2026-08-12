<?php

namespace Database\Factories;

use App\Models\OpenSourceProject;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpenSourceProjectFactory extends Factory
{
    protected $model = OpenSourceProject::class;

    public function definition(): array
    {
        $repo = fake()->unique()->slug();

        return [
            'name' => fake()->words(2, true),
            'github_url' => "https://github.com/org/{$repo}",
            'github_owner' => 'org',
            'github_repo' => $repo,
            'description' => fake()->sentence(),
            'stars' => fake()->numberBetween(100, 50000),
            'forks' => fake()->numberBetween(10, 5000),
            'watchers' => fake()->numberBetween(10, 1000),
            'contributors_count' => fake()->numberBetween(1, 500),
            'primary_language' => fake()->randomElement(['PHP', 'Python', 'JavaScript', 'Go', 'Rust']),
            'topics' => ['web', 'framework'],
            'license' => fake()->randomElement(['MIT', 'Apache-2.0', 'GPL-3.0']),
            'last_commit_at' => fake()->dateTimeBetween('-1 month', 'now'),
            'github_created_at' => fake()->dateTimeBetween('-5 years', '-1 year'),
            'category' => fake()->randomElement(['framework', 'database', 'devtool']),
            'self_hostable' => fake()->boolean(),
            'has_commercial_version' => fake()->boolean(),
        ];
    }
}
