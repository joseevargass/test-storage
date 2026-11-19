<?php

namespace Database\Factories;

use App\Models\Doc;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Doc>
 */
class DocFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'path' => 'docs/'.fake()->uuid().'.pdf',
        ];
    }
}
