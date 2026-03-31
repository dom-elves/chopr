<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShareRequest extends FormRequest
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
            'debt_id' => ['required', 'exists:debts,id'],
            'group_user_id' => ['required', 'exists:group_users,id'],
            'share_name' => ['sometimes', 'string'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'name' => ['nullable', 'string'],
            'currency' => ['required', 'string', 'size:3'],
        ];
    }

    public function messages()
    {
        return [
            'group_user_id' => 'Please select a user from the dropdown',
            'amount.min' => 'Please enter a valid amount',
        ];
    }
}
