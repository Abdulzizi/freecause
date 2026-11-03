<?php

namespace App\Http\Requests\Admin;

use App\Models\Petition;
use App\Rules\YoutubeVideo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminPetitionSaveRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // An unchanged stored value passes, so legacy values saved under the old rules do not block unrelated edits.
        $youtube = ['nullable', 'string', 'max:200'];
        if ($this->input('youtube_url') !== Petition::query()->whereKey($this->input('id'))->value('youtube_url')) {
            $youtube[] = new YoutubeVideo;
        }

        return [
            'id' => ['required', 'integer', 'exists:petitions,id'],
            'locale' => ['required', 'string', Rule::exists('languages', 'code')->where('is_active', 1)],
            'status' => ['required', 'in:draft,published'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255'],
            'text' => ['nullable', 'string'],
            'goal_signatures' => ['nullable', 'integer', 'in:50,100,1000,5000,10000,50000,100000,500000,1000000,10000000'],
            'is_active' => ['nullable'],
            'is_featured' => ['nullable'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'tags' => ['nullable', 'string', 'max:255'],
            'youtube_url' => $youtube,
            'target' => ['nullable', 'string', 'max:255'],
            'community' => ['nullable', 'string', 'max:255'],
            'community_url' => ['nullable', 'url', 'max:500'],
            'city' => ['nullable', 'string', 'max:120'],
            'created_at' => ['nullable', 'date'],
            'cover_image' => ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,gif,webp'],
            'remove_image' => ['nullable'],
        ];
    }
}
