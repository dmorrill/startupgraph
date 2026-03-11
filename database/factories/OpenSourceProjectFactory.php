<?php

namespace Database\Factories;

use App\Models\OpenSourceProject;
use Illuminate\Database\Eloquent\Factories\Factory;

class OpenSourceProjectFactory extends Factory
{
    protected $model = OpenSourceProject::class;

    public function definition(): array
    {
        $owner = fake()->slug();
        $repo = fake()->slug();

        return [
            'name' => fake()->words(2, true),
            'github_url' => "https://github.com/{$owner}/{$repo}",
            'github_owner' => $owner,
            'github_repo' => $repo,
            'description' => fake()->sentence(),
            'stars' => fake()->numberBetween(0, 50000),
            'forks' => fake()->numberBetween(0, 5000),
            'watchers' => fake()->numberBetween(0, 10000),
            'primary_language' => fake()->randomElement(['PHP', 'Python', 'Go', 'Rust', 'TypeScript', null]),
            'self_hostable' => fake()->boolean(),
            'has_commercial_version' => fake()->boolean(),
        ];
    }
}
