<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUserLevelStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => strtolower(trim((string) $this->input('name')))]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'alpha_dash', 'max:50', 'unique:user_levels,name', Rule::notIn(['admin', 'superadmin', 'system', 'root'])],
            'visible_name' => ['required', 'string', 'max:100'],
        ];
    }
}
