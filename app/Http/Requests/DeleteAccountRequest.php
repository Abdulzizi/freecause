<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'confirm_delete' => ['required', 'in:1'],
        ];

        if (! $this->user()?->isGoogleOnly()) {
            $rules['password'] = ['required', 'current_password:web'];
        }

        return $rules;
    }
}
