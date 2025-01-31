<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendShareRequest extends FormRequest
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
            'paid_amount' => ['required', 'exists:shares,amount', 'numeric'],
            'share_id' => ['required', 'exists:shares,id'],
        ];
    }

    public function messages()
    {
        return [

        ];
    }
}
