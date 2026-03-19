<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Debt;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Comment>
 */
class CommentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * As a comment can't be made without a group_user_id, default to a random one relative to the debt id.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'content' => $this->faker->sentence(),
            'group_user_id' => function (array $attributes) {
                return Debt::find($attributes['debt_id'])->group->groupUsers->random()->id;
            },
        ];
    }
}
