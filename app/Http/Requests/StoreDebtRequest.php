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
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'regex:/^\d+(\.\d{1,2})?$/'],
            'split_even' => ['required', 'boolean'],
            'group_user_values' => ['required', 'array', 'min:1'],
        ];
    }

    public function messages()
    {
        return [
            'group_id.required' => 'Group not found.',
            'amount.min' => 'The total :attribute must be at least 0.01.',
            'amount.regex' => 'The :attribute must be a number with up to 2 decimal places.',
            'name.required' => 'The debt name is required.',
            'group_user_values' => 'Please select at least one user and add a valid amount.',
        ];
    }
}
