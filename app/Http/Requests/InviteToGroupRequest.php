<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsGroupOwner;

class InviteToGroupRequest extends FormRequest
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
            'group_id' => ['exists:groups,id', new IsGroupOwner],
            'user_id' => ['exists:users,id'],
            'recipients' => ['required','array'],
            'body' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.exists' => 'Group does not exist',
            'recipients.required' => 'Please enter one or more email addresses',
        ];
    }
}
