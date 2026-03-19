<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Comment;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->sentence(),
        ];
    }

    /**
     * As a comment can't be created without a debt, randomise who created it.
     */
    public function configure(): static
    {
        return $this->afterMaking(function(Comment $comment) {
            $comment->group_user_id = $comment->debt->group->groupUsers->random()->id;
        });
    }
}
