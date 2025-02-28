<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsShareOwner;

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
            'share_id' => ['required', 'integer', 'exists:shares,id'],
            'sent' => ['boolean'],
            'seen' => ['boolean'],
            'group_user_id' => ['required', 'integer', 'exists:group_users,id', new IsShareOwner()],
        ];
    }
}
