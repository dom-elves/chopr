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
            'amount' => random_int(1,999) + round(100/random_int(100,1000), 2),
            // todo: update this to not always split even, but find a way to randomly chunk debts
            'split_even' => 1,
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
        return 'test2';
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
