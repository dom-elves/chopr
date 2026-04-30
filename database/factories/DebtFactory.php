<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\Comment;
use Faker\Factory as Faker;
use Brick\Money\Money;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
     * 
     * Amount is stored as minor units and is accessed as Money object
     * in the Cash cast.
     *
     * @return array<string, mixed>
     */

    public function definition(): array
    {
        $nouns = file(base_path('app/TextFiles/nouns.txt'), FILE_IGNORE_NEW_LINES);;
        $faker = Faker::create();
        $random_noun = $faker->randomElement($nouns);

        return [
            'name' => $random_noun,
            'amount' => rand(1000, 10000),
            'split_even' => rand(0,1),
            'cleared' => 0,
            'currency' => 'GBP',
        ];
    }

    /**
     * As a debt can be creating bith both/neither/one of group_user_id & group_id,
     * there are some conditions to run through after the debt has been created.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (Debt $debt) {
            if (!$debt->group_id && !$debt->group_user_id) {
                $group = Group::all()->random();

                $debt->group_id = $group->id;
                $debt->group_user_id = $group->groupUsers->random()->id;
            } elseif (!$debt->group_id) {
                $debt->group_id = $debt->groupUser->group->id;
            } elseif (!$debt->group_user_id) {
                $debt->group_user_id = $debt->group->groupUsers->random()->id;
            }
        })->afterCreating(function (Debt $debt) {
            Share::factory()->create([
                'debt_id' => $debt->id,
                'group_user_id' => $debt->group_user_id,
                'amount' => $debt->amount->getMinorAmount()->toInt(),
            ]);
        });
    }

    /**
     * Custom withShares() so some randomisation can be created.
     * Debts are created with a share by default, so available group users are filtered.
     * 
     * A bit clunky, but deleted the existing shares & ledgers if withShares is called,
     * simpler than adding a bunch of logic to see if a share already exists and then,
     * changing/removing the amounts etc etc
     */
    public function withShares() {
        return $this->afterCreating(function(Debt $debt) {

            $debt->shares->each(function ($share) {
                $share->ledgerEntry->delete();
                $share->delete();
            });

            $group_users = $debt->group->groupUsers
                    ->random(rand(2, $debt->group->groupUsers->count()));

            if ($debt->split_even) {
                $this->splitEvenShares($debt, $group_users);
            } else {
                $this->chunkSharesRandomly($debt, $group_users);
            }
        });
    }

    /**
     * Add between 0 and 5 comments to a debt on creation.
     */
    public function withComments(): static
    {
        return $this->afterCreating(function (Debt $debt) {
            Comment::factory()
                ->count(rand(0, 5))
                ->create([
                    'debt_id' => $debt->id,
            ]);
        });
    }
    /**
     * split() is a brick/money method that evenly splits a value into money objects
     */
    private function splitEvenShares(Debt $debt, $group_users): void
    {
        $money = $debt->amount->split($group_users->count());

        foreach ($group_users as $key => $group_user) {
            Share::factory()->create([
                'group_user_id' => $group_user->id,
                'debt_id' => $debt->id,
                'amount' => $money[$key]->getMinorAmount()->toInt(),
            ]);
        }
    }

    private function chunkSharesRandomly(Debt $debt, $group_users): void
    {
        $total = $debt->amount->getMinorAmount()->toInt();
        $count = $group_users->count();

        $breakpoints = array_map(fn() => rand(1, $total - 1), range(1, $count - 1));
        sort($breakpoints);

        $points = [0, ...$breakpoints, $total];
        $shares = array_map(fn($key) => $points[$key + 1] - $points[$key], range(0, $count - 1));

        foreach ($group_users as $key => $group_user) {
            Share::factory()->create([
                'group_user_id' => $group_user->id,
                'debt_id' => $debt->id,
                'amount' => Money::ofMinor($shares[$key], $debt->amount->getCurrency()),
            ]);
        }
    }
}
