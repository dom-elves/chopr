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
        
        $user = Auth::user();
        $debt = Debt::findOrFail($value);

        if ($debt->user_id !== $user->id) {
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
