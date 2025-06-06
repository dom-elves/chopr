<?php

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
 * select a random amount of users
 * split the debt randomly between the users
 * the last user remaining takes the last share
 * return the key value pair of user_ids and share amounts
 */
function selectRandomGroupUsers($users, $debt_total, $split_even) {
    $users = $users->random(rand(2, $users->count()));

    if (!$split_even) {
        while($users->count() > 0) {
            // if there's only one user left, they take the remaining debt
            if ($users->count() === 1) {
                $user = $users->pop();

                $user_shares[] = [
                    'user_id' => $user->id,
                    'name' => 'share for user ' . $user->id,
                    'amount' => $debt_total,
                ];
            // otherwise, we take the last user and give them a random chunk of the debt
            // then subtract that from the debt total
            } else {
                $user = $users->pop();

                $share_amount = rand(1, $debt_total / $users->count());

                $user_shares[] = [
                    'user_id' => $user->id,
                    'name' => 'share for user ' . $user->id,
                    'amount' => $share_amount,
                ];

                $debt_total -= $share_amount;
            } 
        }
    } else {
        // because the rounding is done on the frontend, we have to replicate it here
        $share_amount = floor(($debt_total / $users->count()) * 100) / 100;
        $remainder = $debt_total - ($share_amount * $users->count());
        foreach ($users as $user) {
            $user_shares[] = [
                'user_id' => $user->id,
                'name' => 'share for user ' . $user->id,
                'amount' => $share_amount,
            ];
        }

        $user_shares[0]['amount'] += $remainder;
    }

    return $user_shares;
}