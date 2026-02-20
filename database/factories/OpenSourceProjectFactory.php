<?php

namespace Database\Factories;

use App\Models\OpenSourceProject;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpenSourceProjectFactory extends Factory
{
    protected $model = OpenSourceProject::class;

    public function definition(): array
    {
        $owner = fake()->userName();
        $repo = fake()->slug(2);

        return [
            'name' => $repo,
            'github_url' => "https://github.com/{$owner}/{$repo}",
            'github_owner' => $owner,
            'github_repo' => $repo,
            'description' => fake()->sentence(),
            'stars' => fake()->numberBetween(0, 50000),
            'forks' => fake()->numberBetween(0, 5000),
            'watchers' => fake()->numberBetween(0, 1000),
            'contributors_count' => fake()->numberBetween(1, 500),
            'primary_language' => fake()->randomElement(['PHP', 'Python', 'JavaScript', 'Go', 'Rust']),
            'topics' => fake()->randomElements(['web', 'api', 'cli', 'database', 'framework'], 3),
            'license' => fake()->randomElement(['MIT', 'Apache-2.0', 'GPL-3.0', 'BSD-3-Clause']),
            'last_commit_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'github_created_at' => fake()->dateTimeBetween('-5 years', '-1 year'),
            'category' => fake()->randomElement(['database', 'framework', 'tool', 'library']),
            'self_hostable' => fake()->boolean(),
            'has_commercial_version' => fake()->boolean(),
            'commercial_url' => fake()->optional()->url(),
        ];
    }
}
