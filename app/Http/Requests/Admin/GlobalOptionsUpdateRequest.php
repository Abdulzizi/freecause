<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class GlobalOptionsUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'base_url' => ['nullable', 'url', 'max:255'],
            'short_base_url' => ['nullable', 'url', 'max:255'],
            'email_to_staff' => ['nullable', 'email', 'max:190'],
            'email_from' => ['nullable', 'email', 'max:190'],
            'smtp_encryption' => ['nullable', 'in:tls,ssl,'],
            'max_featured_petitions_per_country' => ['nullable', 'integer', 'min:1', 'max:100'],
            'special_debug_ip' => ['nullable', 'ip'],
        ];
    }
}
