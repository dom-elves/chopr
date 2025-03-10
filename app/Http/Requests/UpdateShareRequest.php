<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsShareOwner;
use App\Rules\IsDebtOwner;
use App\Models\Share;

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
                    $validator = new IsDebtOwner;
                    if (!$validator->validate($attribute, $value, $fail)) {
                       return $fail;
                    }
                }
            }],
            'sent' => ['sometimes', 'boolean'],
            'seen' => ['sometimes', 'boolean'],
            'group_user_id' => ['required', 'integer', 'exists:group_users,id'],
        ];
    }
}
