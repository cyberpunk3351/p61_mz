<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = $this->faker->sentence();

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'content' => $this->faker->paragraphs(3, true),
            'slug' => Str::slug($title) . '-' . time() . rand(1000, 9999),
            'published' => $this->faker->boolean(80), // 80% chance of being published
            'published_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
