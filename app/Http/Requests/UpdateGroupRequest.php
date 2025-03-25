<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Group;
use App\Rules\IsGroupOwner;

class UpdateGroupRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // not sure there's a way to specifically handle 403 exceptions with inerta
        // as inertia just gets in the way
        // $group = Group::findOrFail($this->group_id);
        
        // return $this->user()->can('update', $group);
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
            'id' => ['required', 'integer', 'exists:groups,id', new IsGroupOwner],
            'name' => ['required', 'string', 'max:255'],
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
