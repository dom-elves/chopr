<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

use Faker\Factory as Faker;
/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Group>
 */
class GroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $faker = Faker::create();

        // random verbs & nouns to make group name
        $verbs = $this->getWords('app/TextFiles/verbs.txt');
        $nouns = $this->getWords('app/TextFiles/nouns.txt');
        $random_verb = $faker->randomElement($verbs);
        $random_noun = $faker->randomElement($nouns);

        return [
            'name' => "The {$random_verb} {$random_noun}",
            'user_id' => User::all()->random()->id,
        ];
    }

    private function getWords($path) {
        return file(base_path($path), FILE_IGNORE_NEW_LINES);
    }
}
