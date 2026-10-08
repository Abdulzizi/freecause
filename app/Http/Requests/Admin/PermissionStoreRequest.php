<?php

namespace App\Http\Requests\Admin;

use App\Models\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PermissionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module' => ['required', 'string', Rule::in(array_keys(Permission::matrix()))],
            'level_id' => ['required', 'integer', 'exists:user_levels,id'],
            'actions' => ['array'],
            'actions.*' => [Rule::in(Permission::matrix()[$this->input('module')] ?? [])],
        ];
    }
}
