<?php

namespace App\Http\Requests\Admin;

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
            'goal_signatures' => ['nullable', 'integer', 'in:50,100,1000,5000,10000,50000,100000,500000,1000000,10000000'],
        ];
    }
}
