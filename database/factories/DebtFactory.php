<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Group;
use App\Models\Debt;
use App\Models\Share;
use App\Models\Comment;
use Faker\Factory as Faker;

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
            'amount' => rand(100, 10000),
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
                $debt->group_id = $debt->group_user->group->id;
            } elseif (!$debt->group_user_id) {
                $debt->group_user_id = $debt->group->groupUsers->random()->id;
            }
        });
    }

    /**
     * Custom withShares() so some randomisation can be created.
     */
    public function withShares() {
        return $this->afterCreating(function(Debt $debt) {

            if ($debt->split_even) {
                $this->splitEvenShares($debt);
            } else {
                $this->chunkSharesRandomly($debt);
            }
        });
    }

    /**
     * Add between 0 and 5 comments to a debt on creation.
     */
    public function withComments(): static
    {
        return $this->afterCreating(function (Debt $debt) {
            $count = rand(0, 5);

            if ($count > 0) {
                Comment::factory()
                    ->count($count)
                    ->create([
                        'debt_id' => $debt->id,
                    ]);
            }
        });
    }
    /**
     * split() is a brick/money method that evenly splits a value into money objects
     */
    private function splitEvenShares($debt) {
        $debt_group_users = $debt->group->groupUsers->random(rand(2, $debt->group->groupUsers->count()));
        $money = $debt->amount->split($debt_group_users->count());

        foreach ($debt_group_users as $key => $debt_group_user) {
            Share::factory()->calcTotal()->create([
                'group_user_id' => $debt_group_user->id,
                'debt_id' => $debt->id,
                'amount' => $money[$key],
            ]);
        }
    }

    private function chunkSharesRandomly($debt) {
        $debt_group_users = $debt->group->groupUsers->random(rand(2, $debt->group->groupUsers->count()));
        $total = $debt->amount->getMinorAmount()->toInt();

        $count = $debt_group_users->count();
        $chunk = intdiv($total, $count);
        
        foreach ($debt_group_users as $debt_group_user) {
            // give some variance to chunks 
            $split = rand($chunk - 1000, $chunk + 1000);
            // give the last user the remainder of the debt
            $share_amount = $count === 1 ? $total : $split;

            Share::factory()->calcTotal()->create([
                'group_user_id' => $debt_group_user->id,
                'debt_id' => $debt->id,
                'amount' => $share_amount,
            ]);
           
            // take away the split each time
            $total -= $split;
            $count--;
        }
    }
}
