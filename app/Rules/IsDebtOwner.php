<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupUser;
use App\Models\Debt;
use App\Models\Share;

class IsDebtOwner implements ValidationRule
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

        // find the logged in user
        $logged_in_user_id = Auth::user()->id;
        // find their group users
        $group_user_ids = GroupUser::where('user_id', $logged_in_user_id)->get()->pluck('id')->toArray();

        // the debt
        $debt = Debt::findOrFail($value);

        // if the group user id of the debt does not appear in the logged in user's group users
        // that means they do not own this share, therefore can not update it
        if (!in_array($debt->collector_group_user_id, $group_user_ids)) {
            $item = '';

            // different wording depending if we are updating a share or debt 
            if (request()->route()->getName() === 'share.update') {
                $item = 'share';
            } else {
                $item = 'debt';
            }

            $fail('You do not have permission to edit or delete this ' . $item);
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
