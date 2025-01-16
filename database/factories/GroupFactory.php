<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

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

        $verbs = file(base_path('app/TextFiles/verbs.txt'), FILE_IGNORE_NEW_LINES);
        $random_verb = $faker->randomElement($verbs);

        $nouns = file(base_path('app/TextFiles/nouns.txt'), FILE_IGNORE_NEW_LINES);
        $random_noun = $faker->randomElement($nouns);

        return [
            'name' => "The {$random_verb} {$random_noun}",
        ];
    }
}
