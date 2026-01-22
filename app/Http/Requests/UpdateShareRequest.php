<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
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
            'sent' => ['sometimes', 'boolean', function($attribute, $value, $fail) {
                if ($this->user()->cannot('updateSent', $this->route('share'))) {
                    return $fail('gtgggg');
                }
            }],
            'seen' => ['sometimes', 'boolean'],
            'amount' => ['sometimes', 'numeric'],
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
        ];
    }
}
