<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GroupUser;
use App\Models\Alias;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GroupUser>
 */
class GroupUserFactory extends Factory
{
    /**
     * Define the model's default state.
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
            foreach ($group_user->group->users as $user) {
              
                    Alias::factory()->create([
                        'user_id' => $user->id,
                        'group_user_id' => $group_user->id,
                        'alias' => fake()->name(),
                    ]);
              
            }
        });
    }
}
