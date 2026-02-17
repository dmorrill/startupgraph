<?php

namespace Database\Seeders;

use App\Models\Tag;
use Illuminate\Database\Seeder;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Tag::DEFAULT_TAGS as $type => $names) {
            foreach ($names as $name) {
                Tag::firstOrCreate(
                    ['slug' => \Illuminate\Support\Str::slug($name)],
                    ['name' => $name, 'type' => $type]
                );
            }
        }
    }
}
