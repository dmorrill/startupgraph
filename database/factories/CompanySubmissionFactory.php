<?php

namespace Database\Factories;

use App\Models\CompanySubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

class CompanySubmissionFactory extends Factory
{
    protected $model = CompanySubmission::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'url' => fake()->url(),
            'description' => fake()->sentence(),
            'builder_name' => fake()->name(),
            'tech_stack' => 'Laravel, Vue.js',
            'submitter_email' => fake()->safeEmail(),
            'source_url' => fake()->url(),
            'status' => 'pending',
        ];
    }
}
