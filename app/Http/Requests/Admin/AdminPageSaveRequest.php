<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminPageSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page_id' => ['nullable', 'integer', 'exists:pages,id'],
            'locale' => ['required', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
            'title' => ['required', 'string', 'max:190'],
            'slug' => ['nullable', 'string', 'max:190'],
            'content' => ['nullable', 'string'],
        ];
    }

    protected function getRedirectUrl(): string
    {
        return route('admin.pages', array_filter([
            'locale' => $this->input('locale'),
            'select' => $this->input('page_id'),
        ], fn ($value) => $value !== null && $value !== ''));
    }
}
