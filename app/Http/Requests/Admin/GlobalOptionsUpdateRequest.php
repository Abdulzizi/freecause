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

            'ads_host' => ['nullable', 'url', 'max:255'],
            'ads_slot_leaderboard_key' => ['nullable', 'string', 'max:190'],
            'ads_slot_rectangle_key' => ['nullable', 'string', 'max:190'],
            'ads_slot_skyscraper_key' => ['nullable', 'string', 'max:190'],
            'ads_slot_banner468_key' => ['nullable', 'string', 'max:190'],
            'ads_slot_native_key' => ['nullable', 'string', 'max:190'],
            'ads_slot_mobile_key' => ['nullable', 'string', 'max:190'],
            'ads_slot_mobile_feed_key' => ['nullable', 'string', 'max:190'],
            'ads_slot_mobile_inline_key' => ['nullable', 'string', 'max:190'],
            'ads_slot_mobile_mini_key' => ['nullable', 'string', 'max:190'],
            'ads_slot_mobile_strip_key' => ['nullable', 'string', 'max:190'],
        ];
    }
}
