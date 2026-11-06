<?php

namespace App\Http\Requests;

use App\Models\Petition;
use App\Rules\MinCharacters;
use App\Rules\MinWords;
use App\Rules\SafeExternalImageUrl;
use App\Rules\TagList;
use App\Rules\YoutubeVideo;
use App\Support\Countries;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PetitionRequest extends FormRequest
{
    private ?Petition $petition = null;

    public function authorize(): bool
    {
        if ($this->routeIs('petition.store')) {
            return true;
        }

        // Missing petition answers 404 before any validation or remote image fetch.
        $petition = $this->currentPetition() ?? abort(404);

        return auth()->check() && (int) $petition->user_id === (int) auth()->id();
    }

    private function currentPetition(): ?Petition
    {
        return $this->petition ??= Petition::query()->whereKey($this->route('id'))->first(['user_id', 'image_url', 'youtube_url']);
    }

    public function rules(): array
    {
        $creating = $this->routeIs('petition.store');

        $image = $creating
            ? ['nullable', 'file', 'image', 'max:4096', 'mimes:jpg,jpeg,png,gif,webp']
            : ['nullable', 'image', 'max:4096', 'mimes:jpg,jpeg,png,gif,webp'];

        $current = $creating
            ? null
            : $this->currentPetition();
        $currentUrl = $current?->image_url;

        // An unchanged stored value passes, so legacy values saved under the old rules do not block unrelated edits.
        $youtube = ['nullable', 'string', 'max:200'];
        if ($this->input('youtube') !== $current?->youtube_url) {
            $youtube[] = new YoutubeVideo;
        }

        $imageUrl = ['nullable', 'max:500'];
        if ($this->input('image_url') !== $currentUrl) {
            array_push($imageUrl, 'url', 'regex:/^https?:\/\//i');
            if (! $this->hasFile('image') && filled($this->input('image_url'))) {
                $imageUrl[] = new SafeExternalImageUrl(4 * 1024 * 1024);
            }
        }

        return [
            'title' => ['required', 'string', 'max:255', new MinWords(3, 'Title')],
            // 65535 = the legacy MySQL TEXT limit, so the longest legacy descriptions still pass unrelated edits.
            'description' => ['bail', 'required', 'string', 'max:65535', new MinCharacters(30, 'Text')],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'tags' => ['nullable', 'string', 'max:255', new TagList(10, 30)],
            'image' => $image,
            'image_url' => $imageUrl,
            'youtube' => $youtube,
            'target' => ['nullable', 'string', 'max:255'],
            'community' => ['nullable', 'string', 'max:255'],
            'community_url' => ['nullable', 'url', 'max:500', 'regex:/^https?:\/\//i'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'size:2', Rule::in(Countries::codes())],
        ] + ($creating
            ? ['goal_signatures' => ['required', 'integer', Rule::in(Petition::GOALS)]]
            : ['remove_image' => ['nullable', 'boolean']]);
    }

    public function messages(): array
    {
        return [
            'description.max' => __('create.err_description_too_long'),
            'image.uploaded' => __('create.err_image_upload_failed'),
        ];
    }
}
