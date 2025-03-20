<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupUser;
use App\Models\Share;

class IsShareOwner implements ValidationRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // rule runs against the 'item' id
        //todo: refactor this & other rules into one rule
        
        $user = Auth::user();
        $share = Share::findOrFail($value);

        // if the group user id of the share does not appear in the logged in user's group users
        // that means they do not own this share, therefore can not update it
        if ($share->user_id !== $user->id) {
            $fail('You do not have permission to edit or delete this share');
        }
    }

    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }
}
