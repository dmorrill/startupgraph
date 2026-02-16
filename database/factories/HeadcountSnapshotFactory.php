<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\HeadcountSnapshot;
use Illuminate\Database\Eloquent\Factories\Factory;

class HeadcountSnapshotFactory extends Factory
{
    protected $model = HeadcountSnapshot::class;

    public function definition(): array
    {
        return [
            'company_id' => Company::factory(),
            'headcount' => fake()->numberBetween(10, 5000),
            'recorded_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'source' => 'linkedin',
        ];
    }
}
