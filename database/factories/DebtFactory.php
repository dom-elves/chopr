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
            // debts, balances etc are now stored in lowest denomination possible
            // e.g. 1000 = £10
            'amount' => random_int(1000,100000) / 100,
            'split_even' => rand(0,1),
            'cleared' => 0,
            'currency' => 'GBP',
        ];
    }

    public function withShares() {
        return $this->afterCreating(function(Debt $debt) {
            $group_users = $debt->group->group_users;

            $debt->user->save();

            if ($debt->split_even) {
                $this->splitEvenShares($debt, $group_users);
            } else {
                $this->chunkSharesRandomly($debt, $group_users);
            }
        });
    }

    private function splitEvenShares($debt, $group_users) {
        // figure out base share and round down
        $floor_split = floor(($debt->amount / $group_users->count()) * 100) / 100;
        // total base shares 
        $total_splits = $floor_split * $group_users->count();
        // find remainder by removing total base shares from original amount
        $remainder = round($debt->amount - $total_splits, 2);
     
        // start a count
        $count = 0;
        foreach ($group_users as $group_user) {
            // create the share
            $share = Share::factory()->calcTotal()->create([
                'user_id' => $group_user->user->id,
                'debt_id' => $debt->id,
                // the first person in the loop gets the remainder, just like in AddDebt component
                'amount' => $count === 0 ? $floor_split + $remainder : $floor_split,
                // debt owner shar automatically set to 'sent'
                // 'sent' => $group_user->user_id === $debt->user_id ? 1 : rand(0, 1),
                'sent' => 0,
                'seen' => 0,
            ]);
            
            $count++;
        }
    }

    private function chunkSharesRandomly($debt, $group_users) {
        // start count 
        $count = $group_users->count();
        // set the total of the debt
        $total = $debt->amount;
        // figure out base share and round down, same as in split even
        $floor_split = floor(($debt->amount / $group_users->count()) * 100) / 100;

        // this way distributes shares until money runs out
        foreach ($group_users as $group_user) {
            // if we're out of money to distrubte, ignore rest of group_users
            if ($total <= 0) {
                return;
            }
            
            // figure out a split +/- 1000 of the even split
            $split = rand($floor_split * 100 - 1000, $floor_split * 100 + 1000) / 100;

            // create the share
            $share = Share::factory()->calcTotal()->create([
                'user_id' => $group_user->user->id,
                'debt_id' => $debt->id,
                // give the last user the rest of the money
                'amount' => $count === 1 ? $total : $split,
                // debt owner shar automatically set to 'sent'
                // 'sent' => $group_user->user_id === $debt->user_id ? 1 : rand(0, 1),
                'sent' => 0,
                'seen' => 0,
            ]);
           
            // take away the split each time
            $total -= $split;
            $count--;
        }
    }
}
