<?php

namespace App\Http\Requests\Admin;

use App\Models\Language;
use App\Support\MailText;
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
        $keys = array_keys(MailText::defaults('en'));
        $emailRules = ['email_templates' => ['sometimes', 'array:'.implode(',', $keys)]];
        foreach (MailText::defaults('en') as $key => $default) {
            $emailRules['email_templates.'.$key] = ['sometimes', 'required', 'string', 'max:4000', function ($attribute, $value, $fail) use ($default, $key) {
                preg_match_all('/:[a-z_]+/', $default, $matches);
                foreach ($matches[0] as $token) {
                    if (! str_contains($value, $token)) {
                        $fail('Keep the placeholder '.$token.' in this email text.');
                    }
                }
                if (str_ends_with($key, '_subject') && preg_match('/[\r\n]/', $value)) {
                    $fail('Email subjects must be a single line.');
                }
            }];
        }

        return array_merge($emailRules, [
            'locale' => ['required', Rule::in(Language::where('is_active', true)->pluck('code')->all())],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'meta_keywords' => ['nullable', 'string', 'max:500'],
            'exclude_most_read' => ['nullable', 'regex:/^\s*\d+(\s*,\s*\d+)*\s*$/'],
        ]);
    }
}
