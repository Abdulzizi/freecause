<?php

namespace App\Http\Requests;

use App\Models\Petition;
use App\Rules\MinCharacters;
use App\Rules\MinWords;
use App\Rules\SafeExternalImageUrl;
use App\Rules\TagList;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PetitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $creating = $this->routeIs('petition.store');

        $image = $creating
            ? ['nullable', 'file', 'image', 'max:4096', 'mimes:jpg,jpeg,png,gif,webp']
            : ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,gif,webp'];

        $currentUrl = $creating
            ? null
            : Petition::query()->whereKey($this->route('id'))->value('image_url');

        $imageUrl = ['nullable', 'url', 'max:500', 'regex:/^https?:\/\//i'];
        if (! $this->hasFile('image')
            && filled($this->input('image_url'))
            && $this->input('image_url') !== $currentUrl) {
            $imageUrl[] = new SafeExternalImageUrl(4 * 1024 * 1024);
        }

        return [
            'title' => ['required', 'string', 'max:255', new MinWords(3, 'Title')],
            'description' => ['required', 'string', new MinCharacters(30, 'Text')],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'tags' => ['nullable', 'string', 'max:255', new TagList(10, 30)],
            'image' => $image,
            'image_url' => $imageUrl,
            'youtube' => ['nullable', 'string', 'max:200', 'regex:/^(?:https?:\/\/(?:www\.)?(?:youtube\.com\/watch\?v=|youtu\.be\/)[\w_-]+|[\w_-]+)$/i'],
            'target' => ['nullable', 'string', 'max:255'],
            'community' => ['nullable', 'string', 'max:255'],
            'community_url' => ['nullable', 'url', 'max:500', 'regex:/^https?:\/\//i'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'size:2'],
        ] + ($creating
            ? ['goal_signatures' => ['required', 'integer', 'in:50,100,1000,5000,10000,50000,100000,500000,1000000,10000000']]
            : ['remove_image' => ['nullable', 'boolean']]);
    }
}
