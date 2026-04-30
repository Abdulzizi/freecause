@php
    $mode = $mode ?? 'create'; // create|edit
    $isEdit = $mode === 'edit';

    // when editing, controller should pass: $petition, $tr, $locale, $categories
    // when creating, controller should pass: $categories, $locale (and no $petition/$tr)

    $oldTitle = old('title', $tr->title ?? '');
    $oldDesc = old('description', $tr->description ?? '');

    $oldGoal = old('goal_signatures', $petition->goal_signatures ?? null);
    $oldCategory = old('category_id', $petition->category_id ?? null);

    $oldTags = old('tags', $petition->tags ?? '');
    $oldImageUrl = old('image_url', $petition->image_url ?? '');
    $oldYoutube = old('youtube', $petition->youtube ?? '');
    $oldTarget = old('target', $petition->target ?? '');
    $oldCommunity = old('community', $petition->community ?? '');
    $oldCommunityUrl = old('community_url', $petition->community_url ?? '');
    $oldCity = old('city', $petition->city ?? '');
@endphp

@extends('layouts.legacy')

@section('title', $isEdit ? 'Edit petition - xPetition' : 'Create petition - xPetition')

@section('content')
    <section class="py-5">
        <div class="container">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(!$isEdit)
                <h2 class="text-center mb-4">{{ __('petition.create_title') }}</h2>

                <div class="fc-steps mb-5">
                    <div class="fc-step">
                        <span class="fc-step-icon fc-step-1 is-active"></span>
                        <div class="fc-step-text">{{ __('petition.step_create') }}</div>
                    </div>

                    <div class="fc-step">
                        <span class="fc-step-icon fc-step-2"></span>
                        <div class="fc-step-text">{{ __('petition.step_share') }}</div>
                    </div>

                    <div class="fc-step">
                        <span class="fc-step-icon fc-step-3"></span>
                        <div class="fc-step-text">{{ __('petition.step_change') }}</div>
                    </div>
                </div>

                @guest
                    <div class="row g-4 mb-4">
                        <div class="col-lg-6">
                            @include('auth.partials._register_card', ['redirect' => url()->current()])
                        </div>
                        <div class="col-lg-6">
                            @include('auth.partials._login_card', ['redirect' => url()->current()])
                        </div>
                    </div>
                @endguest
            @endif

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <div class="mb-3">
                        <div class="fw-semibold">{{ __('create.petition_data') }}</div>
                        <div style="height:2px;background:#d61f26;width:100%;margin-top:6px;"></div>
                    </div>

                    <form method="POST" action="{{ $isEdit
                        ? lroute('petition.update', ['slug' => $tr->slug, 'id' => $petition->id])
                        : lroute('petition.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">{{ __('form.title') }}</label>
                            <input class="form-control" name="title" value="{{ $oldTitle }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('form.description') }}</label>

                            <div class="fc-markup">
                                <div id="fc-quill-toolbar" class="fc-markup-bar">
                                    <button class="ql-bold" type="button" title="Bold (Ctrl+B)"></button>
                                    <button class="ql-italic" type="button" title="Italic (Ctrl+I)"></button>
                                    <button class="ql-underline" type="button" title="Underline (Ctrl+U)"></button>

                                    <span class="fc-markup-divider"></span>

                                    <button class="ql-list" value="bullet" type="button" title="Bullet list"></button>
                                    <button class="ql-list" value="ordered" type="button" title="Numbered list"></button>

                                    <span class="fc-markup-spacer"></span>
                                </div>

                                <div id="petition_editor" class="fc-quill"></div>

                                <textarea id="petition_description" class="d-none" name="description"
                                    required>{!! $oldDesc !!}</textarea>
                            </div>

                            <div class="fc-markup-hint">allowed: br, p, strong, em, u, ul, ol, li</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">{{ __('form.goal_signatures') }}</label>

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

                            <div class="col-md-6">
                                <label class="form-label">{{ __('form.category') }}</label>
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
                            <label class="form-label">{{ __('form.tags') }}</label>
                            <input class="form-control" name="tags" value="{{ $oldTags }}">
                            <small class="text-muted">{{ __('create.tags_hint') }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('form.image') }}</label>
                            <input type="file" class="form-control" name="image" accept="image/jpeg,image/png">
                            <div class="mt-2">
                                <label class="form-label">{{ __('form.image_external') }}</label>
                                <input class="form-control" name="image_url" value="{{ $oldImageUrl }}"
                                    placeholder="https://">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('form.youtube') }}</label>
                            <input class="form-control" name="youtube" value="{{ $oldYoutube }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('form.target') }}</label>
                            <input class="form-control" name="target" value="{{ $oldTarget }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('form.community') }}</label>
                            <input class="form-control" name="community" value="{{ $oldCommunity }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('form.community_url') }}</label>
                            <input class="form-control" name="community_url" value="{{ $oldCommunityUrl }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">{{ __('form.city') }}</label>
                            <input class="form-control" name="city" value="{{ $oldCity }}">
                        </div>

                        <div class="text-center">
                            @auth
                                <button class="btn btn-danger px-5">{{ $isEdit ? __('create.update') : __('form.submit') }}</button>
                            @else
                                <button class="btn btn-danger px-5" disabled title="Please login first">{{ __('form.submit') }}</button>
                                <div class="text-muted mt-2" style="font-size:14px;">
                                    {{ __('create.login_required') }}
                                </div>
                            @endauth
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('legacy/js-v2/petition-create.js') }}"></script>
@endpush
