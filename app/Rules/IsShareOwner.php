<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use App\Models\GroupUser;

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
        $logged_in_user = Auth::user();
        $requester = GroupUser::findOrFail($value)->user;
        dump('share data', $attribute, $value, $fail);
        dump('share checks', $logged_in_user->id, $requester->id);

        if ($logged_in_user->id != $requester->id) {
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
