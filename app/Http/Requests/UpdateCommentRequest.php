<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsCommentOwner;

class UpdateCommentRequest extends FormRequest
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
            'id' => ['required', 'exists:comments,id', new IsCommentOwner()],
            'debt_id' => ['required', 'exists:debts,id'],
            'content' => ['required', 'string'],
            'user_id' => ['required', 'exists:users,id'],
        ];
    }
}
