<?php

use Brick\Money\Money;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

/**
 * Pick some random users.
 * Create 'cuts' (milestones) of which value to cut the debt at,
 * e.g. on the way to 10000, you may cut at 2932, 4893, 4934 and 8752.
 * 
 * Then create an array ($points) that essentially prepends 0 and appends the debt total,
 * these are now the positions at which you are 'cutting', hence why cuts is made with debt_total -1
 * e.g. your first share will be 2932 (0 to 2932),
 * and your last share will be 1248 (8752 to 10000),
 * basically just the difference between each 'cut'
 * 
 * Then just build out the $shares array, and map into $group_user_shares,
 * as it needs to be the same data structure as on the frontend (minus checked, as it's not necessary here) 
 * 
 * @param \Illuminate\Database\Eloquent\Collection $group_users
 * @param int $debt_total
 * @param bool $split_even
 * @return array
 */
function selectRandomGroupUsers($group_users, $debt_total, $split_even) {
    
    $group_users = $group_users->random(rand(2, $group_users->count()));
    $shares = [];

    if ($split_even) {
        $shares = Money::of($debt_total, 'GBP')->split($group_users->count());
    } else {
        $cuts = [];

        for ($i = 0; $i < $group_users->count() - 1; $i++) {
            $cuts[] = rand(1, $debt_total - 1); 
        }

        sort($cuts);
        $points = array_merge([0], $cuts, [$debt_total]);


        for ($i = 0; $i < $group_users->count(); $i++) {
            $minor_units = $points[$i + 1] - $points[$i];
            $shares[] = Money::of($minor_units, 'GBP');
        }
    }

    $group_user_shares = $group_users->map(function ($group_user, $key) use ($shares, $split_even) {
        // for some reason, using a money object here strips it in the share service
        return [
                'group_user_id' => $group_user->id,
                'name'    => 'share for user ' . $group_user->id,
                'amount'        => $shares[$key]->getMinorAmount()->toInt(),
                'user_name'     => $group_user->user->name,
            ];
    });

    return $group_user_shares->toArray();
}