<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsShareOwner;
use App\Rules\IsDebtOwner;
use App\Models\Share;
use App\Models\Debt;

class UpdateShareRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    { 
        return [
            'id' => ['required', 'integer', 'exists:shares,id', function($attribute, $value, $fail) {
                if ($this->has('sent')) {
                    $validator = new IsShareOwner;
                    if (!$validator->validate($attribute, $value, $fail)) {
                       return $fail;
                    }
                }

                if ($this->has('seen')) {
                    // since item ownership is based off item ids, we need the debt id
                    // to check who owns the debt this share is a part of
                    $value = Share::findOrFail($value)->debt->id;
                    $validator = new IsDebtOwner;
                    if (!$validator->validate($attribute, $value, $fail)) {
                       return $fail;
                    }
                }

                if ($this->has('amount')) {
                    // same as above, share owners can change the amount of any share
                    $value = Share::findOrFail($value)->debt->id;
                    $validator = new IsDebtOwner;
                    if (!$validator->validate($attribute, $value, $fail)) {
                       return $fail;
                    }
                }
            }],
            'sent' => ['sometimes', 'boolean'],
            'seen' => ['sometimes', 'boolean'],
            'amount' => ['sometimes', 'numeric'],
        ];
    }
}
