<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GroupUser;
use App\Models\Alias;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupUser>
 */
class GroupUserFactory extends Factory
{
    /**
     * Define the model's default state.
     * No data necessary to be passed in if being created via withGroupUsers, 
     * as part of group creation.
     * Otherwise, user_id and group_id must be provided.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

        ];
    }

    public function withAliases() {
        return $this->afterCreating(function (GroupUser $group_user) {
            Alias::factory()->create([
                'user_id' => $group_user->group->user_id,
                'group_user_id' => $group_user->id,
                'alias' => fake()->name(),
            ]);
        });
    }
}
