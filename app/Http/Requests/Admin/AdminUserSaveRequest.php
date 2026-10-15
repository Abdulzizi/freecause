<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => strtolower(trim((string) $this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:users,id'],
            'username' => ['nullable', 'string', 'max:120'],
            'first_name' => ['nullable', 'string', 'max:120'],
            'last_name' => ['nullable', 'string', 'max:120'],
            'nickname' => ['nullable', 'string', 'max:120', 'required_if:identify_mode,nick'],
            'city' => ['nullable', 'string', 'max:120'],
            'identify_mode' => ['nullable', 'in:full,name,nick'],
            'password' => ['nullable', 'string', 'min:8'],
            'level' => ['nullable', 'string', 'exists:user_levels,name', 'not_in:banned'],
            'email' => ['nullable', 'email', 'max:190', 'unique:users,email,'.(int) $this->input('id')],
            'locale' => ['nullable', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
            'verified' => ['nullable'],
        ];
    }
}
