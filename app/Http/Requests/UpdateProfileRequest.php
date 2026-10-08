<?php

namespace App\Http\Requests;

use App\Support\Countries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->user();

        return [
            'first_name' => ['required', 'string', 'max:60'],
            'last_name' => ['required', 'string', 'max:60'],
            'nickname' => ['nullable', 'string', 'max:80'],
            'city' => ['nullable', 'string', 'max:80'],
            'country' => ['nullable', Rule::in(Countries::codes())],
            'locale' => [
                'required',
                Rule::exists('languages', 'code')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'identify_mode' => ['nullable', Rule::in(['full', 'name', 'nick'])],
            'new_email' => ['nullable', 'email', 'max:190'],
            'new_email_confirmation' => ['nullable', 'same:new_email'],
            'current_password' => [Rule::requiredIf(fn () => filled($this->input('new_password')) && $user?->hasLocalPassword())],
            'new_password' => ['nullable', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }
}
