<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Faker\Factory as Faker;
use App\Models\Share;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Share>
 */
class ShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // done in the same way as DebtFactory, but only gives some shares a name
        $nouns = file(base_path('app/TextFiles/nouns.txt'), FILE_IGNORE_NEW_LINES);;
        $faker = Faker::create();
        $random_noun = $faker->randomElement($nouns);
        
        return [
            'name' => rand(0,1) ? $random_noun : '',
        ];
    }

    public function calcTotal() {
        return $this->afterCreating(function(Share $share) {
            $user = $share->user;
            
            // // start balance at 0
            // // remove any debts user owns
            // // remove any unpaid shares by the user (excluding shares for owned debts)
            // // add in money paid (sent & seen shares)

            // // $debts = $user->debts;
            // // $total_debts = $debts->sum('amount');

            // $shares = $user->shares;
            // $total_shares_owned = $shares->where('user_id', '!=', $user->id)
            //     ->where('sent', 0)
            //     ->where('seen', 0)
            //     ->sum('amount');

            // $total_paid = $shares->where('user_id', $user->id)
            //     ->where('sent', 1)
            //     ->where('seen', 1)
            //     ->sum('amount');
            
            // $user->total_balance;

            //
            if ($share->sent && $share->seen) {
                $user->total_balance += $share->amount;
            } elseif ($share->sent && !$share->seen) {
                $user->total_balance -= $share->amount;
            } elseif (!$share->sent && !$share->seen) {
                $user->total_balance -= $share->amount;
            };
     
            // being the green... is bad?
            $user->save();
        });
    }
}
