<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GroupUser;
use App\Models\Alias;
use App\Models\User;
use Illuminate\Support\Arr;

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
            'user_id' => Arr::random(User::all()->pluck('id')->toArray()),
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
