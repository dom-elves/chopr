<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsGroupOwner;
use App\Rules\IsUserInGroup;
use App\Models\Invite;

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
            'recipients' => ['required', 'array', 'min:1'],
            'recipients.*' => [new IsUserInGroup($this->all()), function ($attribute, $value, $fail) {
                $invite = Invite::where('recipient', $value)
                    ->where('group_id', $this->group_id)
                    ->where('accepted_at', null)
                    ->where('deleted_at', null)
                    ->first();

                if ($invite) {
                    return $fail("There is already a pending invite to {$value} for this group.");
                }

            }],
            'body' => ['string'],
        ];
    }

    public function messages(): array
    {
        return [
            'group_id.exists' => 'Group does not exist.',
            'recipients.required' => 'Please enter one or more email addresses.',
        ];
    }
}
