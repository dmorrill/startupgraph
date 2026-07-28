<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteFactory extends Factory
{
    protected $model = Note::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'title' => fake()->sentence(4),
            'body' => fake()->paragraph(),
        ];
    }
}
