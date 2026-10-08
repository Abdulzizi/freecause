<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminPetitionTranslateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id' => ['required', 'integer', 'exists:petitions,id'],
            'locale' => ['nullable', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
        ];
    }
}
