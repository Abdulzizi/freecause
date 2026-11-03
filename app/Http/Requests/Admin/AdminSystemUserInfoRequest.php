<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminSystemUserInfoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Match what the controller stores, so the unique check sees the same value.
        if (is_string($this->input('email'))) {
            $this->merge(['email' => strtolower(trim($this->input('email')))]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore(admin_user()?->id)],
            'current_password' => [Rule::requiredIf(fn () => $this->filled('new_password')), 'nullable', 'string'],
            'new_password' => ['nullable', 'confirmed', 'min:8', 'max:72'],
        ];
    }
}
