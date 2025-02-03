<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class IsGroupOwner implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // dump('rule', $attribute, $value);
        // $logged_in_user = Auth::user();
        // $requester = User::findOrFail($value);
        // // dump('match?', $logged_in_user, $requester);
        // if ($logged_in_user->id != $requester->id) {
        //     $fail('You do not have permission to edit this group');
        // }
    }
}
