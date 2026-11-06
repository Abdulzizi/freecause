<?php

namespace App\Http\Requests\Admin;

use App\Models\Petition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminPetitionCreateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'locale' => ['required', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'goal_signatures' => ['nullable', 'integer', Rule::in(Petition::GOALS)],
        ];
    }
}
