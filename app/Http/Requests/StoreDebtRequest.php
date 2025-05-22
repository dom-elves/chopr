<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDebtRequest extends FormRequest
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
            'group_id' => ['required', 'integer', 'exists:groups,id'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
            'split_even' => ['required', 'boolean'],
            // todo: improve this by making appear for each relevant share
            'user_shares' => ['required', 'array', 'min:1', function ($attribute, $value, $fail) {
                    foreach ($value as $share) {
                        if (strlen($share['name']) > 100) {
                            $fail('Share names may not be greater than 100 characters.');
                        }
                    }    
                },],
            // 'user_shares.*.name' => ['string', 'max:100'],
            'currency' => ['required', 'string', 'max:3'],
        ];
    }

    public function messages()
    {
        return [
            'group_id.required' => 'Please select a group.',
            'amount.min' => 'The total :attribute must be at least 0.01.',
            'amount.regex' => 'The :attribute must be a number with up to 2 decimal places.',
            'name.required' => 'The debt name is required.',
            'name.max' => 'The debt name may not be greater than 255 characters.',
            'user_shares.min' => 'Please select at least one user or enter a valid amount.',
            'currency.required' => 'Please select a currency.',
        ];
    }
}
