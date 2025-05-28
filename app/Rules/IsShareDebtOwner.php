<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Support\Facades\Auth;
use App\Models\Debt;

class IsShareDebtOwner implements ValidationRule, DataAwareRule
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];

    /**
     * Run the validation rule. This rule runs against a share, but checks if the user 
     * owns the debt the share is associated with.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = Auth::user();
        $debt = Debt::findOrFail($this->data['debt_id']);

        $operation = '';

        switch ($attribute) {
            case 'seen':
                $operation = 'status';
                break;
            case 'amount':
                $operation = 'amount';
                break;
            case 'name':
                $operation = 'name';
                break;
        }

        if ($debt->user_id !== $user->id) {
            $fail("You do not have permission to update the " . $operation . " of this share");
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
