<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use App\Models\Share;
use App\Models\GroupUser;

class IsShareOwner implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        // dump('shareid', $value);
        // $share_owner_group_user_id = Share::findOrFail($value)->group_user_id;
        // $group_user_user_id = GroupUser::findOrFail($share_owner_group_user_id)->user_id;
        // $user_id = Auth::user()->id;

        // dump($share_owner_group_user_id, $group_user_user_id, $user_id);
        
        // $match = GroupUser::where('user_id', $user_id)
        //     ->where('id', $share_owner_group_user_id)
        //     ->exists();

        // dd($match);
    }
}
