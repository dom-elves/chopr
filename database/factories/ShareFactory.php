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
            
            // having a positive total_balance means you are owned money
            // having a negative total_balance means you owe someone money
            switch ($share) {
                case $share->sent && $share->seen:
                    $user->total_balance -= $share->amount;
                    break;
                case $share->sent && !$share->seen:
                case !$share->sent && !$share->seen:
                    $user->total_balance += $share->amount;
                    break;
            }
     
            $user->save();
        });
    }
}
