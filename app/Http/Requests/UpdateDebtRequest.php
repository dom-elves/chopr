<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsDebtOwner;
use App\Rules\DoesDebtTotalCorrectly;

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
            'id' => ['required', 'numeric', 'exists:debts,id'],
            'amount' => ['required', 'numeric', 'min:0', new IsDebtOwner],
            'name' => ['required', 'string', 'max:255', new IsDebtOwner],
        ];
    }
}
