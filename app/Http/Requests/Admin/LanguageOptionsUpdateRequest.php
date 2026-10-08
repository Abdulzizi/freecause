<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class LanguageOptionsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'locale' => ['required', Rule::in(Language::where('is_active', true)->pluck('code')->all())],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'exclude_most_read' => ['nullable', 'regex:/^\s*\d+(\s*,\s*\d+)*\s*$/'],
        ];
    }
}
