<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsShareOwner;
use App\Rules\IsDebtOwner;
use App\Models\Share;

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
            'sent' => ['sometimes', 'boolean'],
            'seen' => ['sometimes', 'boolean'],
            'group_user_id' => ['required', 'integer', 'exists:group_users,id', function() {
                // as the valiation rules work off group_user_id
                // they have to be checked here, rather than in sent/seen
                if ($this->sent) {
                    new IsShareOwner();
                }

                if ($this->seen) {
                    new IsDebtOwner();
                }
            }],
        ];
    }
}
