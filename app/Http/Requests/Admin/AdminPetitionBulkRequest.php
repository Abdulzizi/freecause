<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminPetitionBulkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', 'string', 'in:publish,unpublish,activate,deactivate,feature,unfeature,ban'],
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct', 'exists:petitions,id'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
