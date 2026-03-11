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
            'submitter_email' => fake()->email(),
            'status' => 'pending',
        ];
    }

    public function approved(): static
    {
        return $this->state(['status' => 'approved']);
    }

    public function rejected(): static
    {
        return $this->state(['status' => 'rejected']);
    }
}
