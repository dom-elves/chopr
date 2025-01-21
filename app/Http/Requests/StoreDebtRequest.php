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
            'group_user_values' => ['required', 'array'],
        ];
    }

    public function messages()
    {
        return [
            'group_id.required' => 'Group not found.',
            'amount.min' => 'The total :attribute must be at least 0.01.',
            'amount.regex' => 'The :attribute must be a number with up to 2 decimal places.',
            'name.required' => 'The debt name is required.',
            'group_user_values' => 'Please enter at least one valid amount.'
        ];
    }

    /**
     * As the Groups are looped over to show each one on the dashboard
     * pass the group_id with errors so errors only display on the relevant component
     * rather than on every instance of the group component
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $validator->errors()->add('group_id', $this->group_id);
        });
    }
}
