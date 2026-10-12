@php
    $mode = $mode ?? 'create';
    $isEdit = $mode === 'edit';

    $oldTitle = old('title', $tr->title ?? '');
    $oldDesc = old('description', $tr->description ?? '');

    $oldGoal = old('goal_signatures', $petition->goal_signatures ?? null);
    $oldCategory = old('category_id', $petition->category_id ?? null);

    $oldTags = old('tags', $petition->tags ?? '');
    $oldImageUrl = old('image_url', $petition->image_url ?? '');
    $oldYoutube = old('youtube', $petition->youtube_url ?? '');
    $oldTarget = old('target', $petition->target ?? '');
    $oldCommunity = old('community', $petition->community ?? '');
    $oldCommunityUrl = old('community_url', $petition->community_url ?? '');
    $oldCity = old('city', $petition->city ?? '');
    $activeAuthTab = old('auth_form') === 'register' ? 'register' : 'login';
@endphp

@extends('layouts.legacy')

@section('title', $isEdit ? __('create.title_edit') : __('create.title_create'))

@push('head')
    <link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet" integrity="sha384-cPa8kzsYWhqpAfWOLWYIw3V0BhPi/m3lrd8tBTPxr2NrYCHRVZ7xy1cEoRGOM/03" crossorigin="anonymous">
    <style>@view-transition { navigation: none; }</style>
@endpush

@section('content')
    <section class="fc-section">
        <x-ui.container class="max-md:px-3!">

            @auth
                @if ($errors->any())
                    <x-ui.alert variant="error">
                        <ul class="mb-0!">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </x-ui.alert>
                @endif
            @endauth

            @if(!$isEdit)
                <h1 class="text-center! mb-6! max-md:text-[1.45rem]! max-md:mb-4!">{{ __('create.heading_create') }}</h1>

                <div class="flex! flex-wrap! items-center! justify-center! gap-[60px]! mb-10! max-md:hidden!" aria-label="Petition creation progress">
                    <div class="flex! items-center! gap-[18px]! min-w-[240px]!" aria-current="step">
                        <span class="inline-block! w-16! h-16! bg-[url('/legacy/images/startpetition-icons.png')]! bg-no-repeat! bg-[length:200%_300%]! bg-[position:100%_0%]!"></span>
                        <div class="text-xl! font-semibold! text-brand! whitespace-nowrap!">{{ __('petition.step_create') }}</div>
                    </div>

                    <div class="flex! items-center! gap-[18px]! min-w-[240px]! opacity-60!">
                        <span class="inline-block! w-16! h-16! bg-[url('/legacy/images/startpetition-icons.png')]! bg-no-repeat! bg-[length:200%_300%]! bg-[position:0%_50%]!"></span>
                        <div class="text-xl! font-medium! text-[#111]! whitespace-nowrap!">{{ __('petition.step_share') }}</div>
                    </div>

                    <div class="flex! items-center! gap-[18px]! min-w-[240px]! opacity-60!">
                        <span class="inline-block! w-16! h-16! bg-[url('/legacy/images/startpetition-icons.png')]! bg-no-repeat! bg-[length:200%_300%]! bg-[position:0%_100%]!"></span>
                        <div class="text-xl! font-medium! text-[#111]! whitespace-nowrap!">{{ __('petition.step_change') }}</div>
                    </div>
                </div>

                @guest
                    <div class="mb-4! max-w-[720px]! mx-auto! [&_.nav-link]:w-full! [&_.nav-link]:text-ink! [&_.nav-link]:font-semibold! max-md:[&_.nav-link]:py-[0.65rem]! max-md:[&_.nav-link]:px-2! max-md:[&_.tab-pane>div]:p-4! max-md:[&_.tab-pane>div]:border-t-0! max-md:[&_.tab-pane>div]:rounded-t-none! max-md:[&_.tab-pane>div]:rounded-b-lg! max-md:[&_.tab-pane>div]:shadow-none! max-md:[&_.form-control]:w-full! max-md:[&_.form-select]:w-full! max-md:[&_.btn]:w-full!">
                        <ul class="nav nav-tabs nav-fill" role="tablist" aria-label="Authentication options">
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link @if($activeAuthTab === 'login') active text-brand! @endif"
                                    id="create-login-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#create-login-pane"
                                    type="button"
                                    role="tab"
                                    aria-controls="create-login-pane"
                                    aria-selected="{{ $activeAuthTab === 'login' ? 'true' : 'false' }}"
                                >
                                    {{ __('auth.login') }}
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link @if($activeAuthTab === 'register') active text-brand! @endif"
                                    id="create-register-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#create-register-pane"
                                    type="button"
                                    role="tab"
                                    aria-controls="create-register-pane"
                                    aria-selected="{{ $activeAuthTab === 'register' ? 'true' : 'false' }}"
                                >
                                    {{ __('auth.register') }}
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div
                                class="tab-pane fade @if($activeAuthTab === 'login') show active @endif"
                                id="create-login-pane"
                                role="tabpanel"
                                aria-labelledby="create-login-tab"
                                tabindex="0"
                            >
                                @include('auth.partials._login_card', ['redirect' => request()->getRequestUri()])
                            </div>
                            <div
                                class="tab-pane fade @if($activeAuthTab === 'register') show active @endif"
                                id="create-register-pane"
                                role="tabpanel"
                                aria-labelledby="create-register-tab"
                                tabindex="0"
                            >
                                @include('auth.partials._register_card', ['redirect' => request()->getRequestUri()])
                            </div>
                        </div>
                    </div>
                @endguest
            @else
                <h1 class="text-center! mb-6! max-md:text-[1.45rem]! max-md:mb-4!">{{ __('create.heading_edit') }}</h1>
            @endif

            @auth
                <div class="relative! flex! flex-col! min-w-0! bg-white! bg-clip-border! rounded-md! border-0! shadow-sm">
                    <div class="grow! shrink! basis-auto! p-6! max-md:p-4!">

                        <div class="mb-4!">
                            <div class="font-semibold!">{{ __('create.petition_data') }}</div>
                            <div class="h-0.5! bg-brand! w-full! mt-1.5!"></div>
                        </div>

                        <form data-petition-form data-draft-locale="{{ app()->getLocale() }}" data-draft-id="{{ $isEdit ? $petition->id : 'new' }}" class="max-md:[&_.row]:[--bs-gutter-y:0.75rem]! max-md:[&_.mb-3]:mb-[0.85rem]! max-md:[&_.form-control]:w-full! max-md:[&_.form-select]:w-full!" method="POST" action="{{ $isEdit
                            ? lroute('petition.update', ['slug' => $tr->slug, 'id' => $petition->id])
                            : lroute('petition.store') }}" enctype="multipart/form-data">
                            @csrf

                            <div class="mb-3">
                                <x-ui.input name="title" :label="__('form.title')" :value="$oldTitle" required data-maxcount="120" />
                            </div>

                            <div class="mb-3">
                                <label class="block! mb-2!">{{ __('form.description') }}</label>

                                <div class="border! border-[#d9d9d9]! rounded! overflow-hidden!">
                                    <div id="fc-quill-toolbar" class="flex! items-center! gap-1.5! p-1.5! bg-[#f3f3f3]! border-b! border-[#d9d9d9]!">
                                        <button class="ql-bold" type="button" title="Bold (Ctrl+B)"></button>
                                        <button class="ql-italic" type="button" title="Italic (Ctrl+I)"></button>
                                        <button class="ql-underline" type="button" title="Underline (Ctrl+U)"></button>

                                        <span class="w-px! h-5! bg-[#cfcfcf]! mx-1.5!"></span>

                                        <button class="ql-list" value="bullet" type="button" title="Bullet list"></button>
                                        <button class="ql-list" value="ordered" type="button" title="Numbered list"></button>

                                        <span class="flex-1!"></span>
                                    </div>

                                    <div id="petition_editor" class="min-h-[260px]! text-sm! leading-[1.45]! [&_.ql-editor]:min-h-[260px]! [&_.ql-editor_ul]:list-disc! [&_.ql-editor_ul]:pl-[22px]! [&_.ql-editor_ol]:list-decimal! [&_.ql-editor_ol]:pl-[22px]! [&_.ql-editor_li]:list-item!"></div>

                                    <textarea id="petition_description" class="hidden!" name="description"
                                        required>{!! $oldDesc !!}</textarea>
                                </div>

                                <div class="block! pt-2! px-0.5! text-[13px]! text-muted!">{{ __('create.markup_hint') }}</div>
                            </div>

                            <div class="row g-3 mb-3">
                                @unless($isEdit)
                                    <div class="col-md-6">
                                        <label class="block! mb-2!">{{ __('form.goal_signatures') }}</label>

                                        <select class="form-select" name="goal_signatures" required>
                                            <option value="">{{ __('create.select_one') }}</option>

                                            @php
                                                $goals = [50, 100, 1000, 5000, 10000, 50000, 100000, 500000, 1000000, 10000000];
                                                $goalLabel = fn($n) => number_format($n, 0, '.', "'") . ' ' . __('create.signatures_sfx');
                                            @endphp

                                            @foreach ($goals as $g)
                                                <option value="{{ $g }}" @selected((int) $oldGoal === (int) $g)>
                                                    {{ $goalLabel($g) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endunless

                                <div class="col-md-6">
                                    <label class="block! mb-2!">{{ __('form.category') }}</label>
                                    <select class="form-select" name="category_id" required>
                                        <option value="">{{ __('create.select_one') }}</option>
                                        @foreach ($categories as $c)
                                            <option value="{{ $c->id }}" @selected((int) $oldCategory === (int) $c->id)>
                                                {{ $c->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <x-ui.input name="tags" :label="__('form.tags')" :value="$oldTags" :hint="__('create.tags_hint')" />
                            </div>

                            <div class="mb-3" data-image-field>
                                <label class="block! mb-2!">{{ __('form.image') }}</label>

                                <p class="text-muted! text-[.875em]! mb-2!" data-image-instructions>
                                    {{ __('create.image_instructions') }}
                                </p>

                                <input type="hidden" name="MAX_FILE_SIZE" value="4194304">

                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                    name="image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"
                                    data-image-input data-max-size="4194304">

                                <small class="text-muted! block! mt-1!" data-image-formats>
                                    {{ __('create.image_formats') }}
                                </small>

                                @error('image')
                                    <div class="invalid-feedback block!">{{ $message }}</div>
                                @enderror

                                <div class="mt-4! hidden!" data-image-preview>
                                    <img src="" alt="{{ __('create.image_preview_alt') }}" class="img-thumbnail max-h-[220px]! w-auto! h-auto! object-contain!" data-image-preview-img>
                                    <div class="flex! items-center! justify-between! mt-2!">
                                        <small class="text-muted!">
                                            <span data-image-name></span>
                                            <span class="ms-1!" data-image-size></span>
                                        </small>
                                        <button type="button" class="fcui-btn fcui-btn--sm fcui-btn--secondary" data-image-remove>
                                            {{ __('create.remove') }}
                                        </button>
                                    </div>
                                </div>

                                @if($isEdit && ($petition->cover_image || $petition->image_url))
                                    <div class="mt-4!">
                                        <img src="{{ $petition->coverUrl() }}" class="img-thumbnail max-h-[220px]!" alt="{{ $tr->title }}">
                                        <div class="block! min-h-6! pl-6! mb-0.5! mt-2!">
                                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove-petition-image">
                                            <label class="form-check-label" for="remove-petition-image">{{ __('create.remove_current_image') }}</label>
                                        </div>
                                    </div>
                                @endif

                                <div class="flex! items-center! text-center! gap-3! my-4! text-muted! font-semibold! text-sm! uppercase! tracking-[0.05em]! before:flex-1! before:h-px! before:bg-border! after:flex-1! after:h-px! after:bg-border! before:content-['']! after:content-['']!" role="separator" aria-label="or">
                                    <span>{{ __('create.or') }}</span>
                                </div>

                                <div class="mt-2!">
                                    <label class="block! mb-2!">{{ __('form.image_external') }}</label>
                                    <input class="form-control @error('image_url') is-invalid @enderror"
                                        name="image_url" value="{{ $oldImageUrl }}" placeholder="https://" data-image-url>
                                    @error('image_url')
                                        <div class="invalid-feedback block!">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <x-ui.input name="youtube" :label="__('form.youtube')" :value="$oldYoutube" />
                            </div>

                            <div class="mb-3">
                                <x-ui.input name="target" :label="__('form.target')" :value="$oldTarget" />
                            </div>

                            <div class="mb-3">
                                <x-ui.input name="community" :label="__('form.community')" :value="$oldCommunity" />
                            </div>

                            <div class="mb-3">
                                <x-ui.input name="community_url" :label="__('form.community_url')" :value="$oldCommunityUrl" />
                            </div>

                            <div class="mb-6!">
                                <x-ui.input name="city" :label="__('form.city')" :value="$oldCity" />
                            </div>

                            <div class="text-center! max-md:[&_.fcui-btn]:w-full!">
                                <span data-autosave-status class="text-[13px]! text-muted! me-3!"></span>
                                <x-ui.button type="submit" variant="danger" class="px-12!"
                                    :loadingText="$isEdit ? __('create.update') : __('create.submitting')">
                                    {{ $isEdit ? __('create.update') : __('form.submit') }}
                                </x-ui.button>
                            </div>
                        </form>

                    </div>
                </div>
            @endauth

        </x-ui.container>
    </section>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js" integrity="sha384-QUJ+ckWz1M+a7w0UfG1sEn4pPrbQwSxGm/1TIPyioqXBrwuT9l4f9gdHWLDLbVWI" crossorigin="anonymous"></script>
    <script src="{{ asset('legacy/js-v2/petition-create.js') }}"></script>
    <script src="{{ asset('legacy/js-v2/petition-autosave.js') }}"></script>
@endpush
