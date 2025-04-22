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

    /**
     * Calc totals after shares are created
     * The idea is that if your total_balance is positive, you are owed money & vice versa
     */
    public function calcTotal() {
        return $this->afterCreating(function(Share $share) {
            $debt_holder = $share->debt->user;
            $share_holder = $share->user;

            switch ($share) {
                case ($share->user_id === $debt_holder->id):
                    // debts start by making the debtor's balance positive
                    // so immediately 'pay yourself' your share
                    $debt_holder->total_balance -= $share->amount;
                    break;
                case ($share->user_id != $debt_holder->id):
                    if ($share->sent) {
                        // a 'sent' share has been paid, so remove it from debtor balance
                        $debt_holder->total_balance -= $share->amount;
                        // and also credit it to the share owner balance
                        $share_holder->total_balance += $share->amount;
                    } else {
                        // if a share hasn't been sent, remove is from the share owner balance
                        // as debtor already has credit, no need to do anything to their balance
                        $share_holder->total_balance -= $share->amount;
                    }
                    
                    break;
                    
                // not sure what to have as a default case
            }

            $debt_holder->save();
            $share_holder->save();
        });
    }
}
