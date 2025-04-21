<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\GroupUser;
use App\Models\Debt;
use App\Models\User;
use App\Models\Share;
use Illuminate\Database\Eloquent\Model;

use Faker\Factory as Faker;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Debt>
 */
class DebtFactory extends Factory
{
    /**
     * Define the model's default state.
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
            'amount' => random_int(100,999) + round(100/random_int(100,1000), 2),
            'split_even' => rand(0,1),
            'cleared' => 0,
            'currency' => 'GBP',
        ];
    }

    public function withShares() {
        return $this->afterCreating(function(Debt $debt) {
            $group_users = $debt->group->group_users;

            if ($debt->split_even) {
                $this->splitEvenShares($debt, $group_users);
            } else {
                $this->chunkSharesRandomly($debt, $group_users);
            }
        });
    }

    private function splitEvenShares($debt, $group_users) {
        // figure out base share and round down
        $rounded_split = floor(($debt->amount / $group_users->count()) * 100) / 100;
        // total base shares 
        $total_splits = $rounded_split * $group_users->count();
        // find remainder by removing total base shares from original amount
        $remainder = round($debt->amount - $total_splits, 2);
        // start a count
        $count = 0;
        foreach ($group_users as $group_user) {
            // using withoutEvents here mimics the way debt creation works in the controller
            Model::withoutEvents(function() use ($group_user, $debt, $rounded_split, $remainder, &$count) {
                // create the share
                $share = Share::factory()->calcTotal()->create([
                    'user_id' => $group_user->user->id,
                    'debt_id' => $debt->id,
                    // the first person in the loop gets the remainder, just like in AddDebt component
                    'amount' => $count === 0 ? $rounded_split + $remainder : $rounded_split,
                    'sent' => rand(0, 1) ? 1 : 0,
                    'seen' => 0,
                ]);

                // figure out the ownership
                $this->shareOwnership($debt, $share);
            });

            $count++;
        }
    }

    private function chunkSharesRandomly($debt, $group_users) {
        // start count 
        $count = $group_users->count();
        // set the total of the debt
        $total = $debt->amount;
        // figure out base share and round down, same as in split even
        $rounded_split = floor(($debt->amount / $group_users->count()) * 100) / 100;

        // this way distributes shares until money runs out
        foreach ($group_users as $group_user) {
            // if we're out of money to distrubte, ignore rest of group_users
            if ($total <= 0) {
                return;
            }
            // figure out a split +/- 10 of the even split, add decimals to simulate realism
            $split = rand(($rounded_split - 10) * 100, ($rounded_split + 10) * 100) / 100;

            // using withoutEvents here mimics the way debt creation works in the controller
            Model::withoutEvents(function() use ($group_user, $debt, $total, $split, &$count) {
                // create the share
                $share = Share::factory()->calcTotal()->create([
                    'user_id' => $group_user->user->id,
                    'debt_id' => $debt->id,
                    // give the last user the rest of the money
                    'amount' => $count === 1 ? $total : $split,
                    'sent' => 0,
                    'seen' => 0,
                ]);

                // figure out the ownership
                $this->shareOwnership($debt, $share);
            });
            // take away the split each time
            $total -= $split;
            $count--;
        }
    }

    private function shareOwnership($debt, $share) {
        // if the user owns the debt, it's sent and seen by default
        if ($debt->user_id === $share->user_id) {
            $share->sent = 1;
            $share->seen = 1;
            $share->save();
        } else {
            // if the share is sent, randomly pick if it's also seen
            $share->sent ? $share->seen = rand(0,1) : $share->seen = 0;
            $share->save();
        }
    }
}
