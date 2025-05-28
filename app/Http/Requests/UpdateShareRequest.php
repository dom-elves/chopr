<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsShareOwner;
use App\Rules\IsDebtOwner;
use App\Rules\IsShareDebtOwner;
use App\Models\Share;
use App\Models\Debt;
use Illuminate\Support\Facades\Auth;

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
            'id' => ['required', 'integer', 'exists:shares,id'],
            'sent' => ['sometimes', 'boolean', new IsShareOwner()],
            'seen' => ['sometimes', 'boolean', new IsShareDebtOwner()],
            'amount' => ['sometimes', 'numeric', new IsShareDebtOwner()],
            'name' => ['nullable', 'string', 'max:255', new IsShareDebtOwner()],
        ];
    }
}
