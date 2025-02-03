<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\IsGroupOwner;

class UpdateGroupRequest extends FormRequest
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
        dump('request', $this->all());
        return [
            // 'id', ['required', 'integer', 'exists:groups,id'],
            // 'name', ['required', 'string', 'max:255'],
            'owner_id' => [new IsGroupOwner()],
        ];
    }
}
