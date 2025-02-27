<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsDebtOwner;

class UpdateDebtRequest extends FormRequest
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
            'debt_id' => ['required', 'numeric', 'exists:debts,id'],
            'amount' => ['required', 'numeric'],
            'name' => ['required', 'string', 'max:255'],
            'owner_group_user_id' => ['required', 'integer', 'exists:group_users,id', new IsDebtOwner]
        ];
    }
}
