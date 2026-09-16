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
    $oldYoutube = old('youtube', $petition->youtube_url ?? '');
    $oldTarget = old('target', $petition->target ?? '');
    $oldCommunity = old('community', $petition->community ?? '');
    $oldCommunityUrl = old('community_url', $petition->community_url ?? '');
    $oldCity = old('city', $petition->city ?? '');
    $activeAuthTab = old('auth_form') === 'register' ? 'register' : 'login';
@endphp

@extends('layouts.legacy')

@section('title', $isEdit ? 'Edit petition - xPetition' : 'Create petition - xPetition')

@push('head')
    <style>
        .fc-auth-tabs {
            max-width: 720px;
            margin-inline: auto;
        }

        .fc-auth-tabs .nav-link {
            width: 100%;
            color: #333;
            font-weight: 600;
        }

        .fc-auth-tabs .nav-link.active {
            color: #d61f26;
        }

        .fc-or-divider {
            display: flex;
            align-items: center;
            text-align: center;
            gap: .75rem;
            margin: 1rem 0;
            color: #888;
            font-weight: 600;
            font-size: .85rem;
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .fc-or-divider::before,
        .fc-or-divider::after {
            content: "";
            flex: 1 1 auto;
            height: 1px;
            background: #e0e0e0;
        }

        @media (max-width: 767.98px) {
            .fc-create-page {
                padding-top: 1.25rem !important;
                padding-bottom: 1.25rem !important;
            }

            .fc-create-page > .container {
                padding-inline: 12px;
            }

            .fc-create-heading {
                font-size: 1.45rem;
                margin-bottom: 1rem !important;
            }

            .fc-desktop-progress {
                display: none !important;
            }

            .fc-auth-tabs .nav-link {
                padding: .65rem .5rem;
            }

            .fc-auth-tabs .tab-pane > div {
                padding: 1rem !important;
                border-top: 0 !important;
                border-radius: 0 0 .5rem .5rem !important;
                box-shadow: none !important;
            }

            .fc-auth-tabs .form-control,
            .fc-auth-tabs .form-select,
            .fc-auth-tabs .btn,
            .fc-petition-form .form-control,
            .fc-petition-form .form-select,
            .fc-submit-bar .btn {
                width: 100%;
            }

            .fc-petition-card > .card-body {
                padding: 1rem !important;
            }

            .fc-petition-form .row {
                --bs-gutter-y: .75rem;
            }

            .fc-petition-form .mb-3 {
                margin-bottom: .85rem !important;
            }

        }
    </style>
@endpush

@section('content')
    <section class="fc-create-page py-5">
        <div class="container">

            @auth
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            @endauth

            @if(!$isEdit)
                <h2 class="fc-create-heading text-center mb-4">Start a free petition</h2>

                <div class="fc-steps fc-desktop-progress mb-5" aria-label="Petition creation progress">
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
                    <div class="fc-auth-tabs mb-4">
                        <ul class="nav nav-tabs nav-fill" role="tablist" aria-label="Authentication options">
                            <li class="nav-item" role="presentation">
                                <button
                                    class="nav-link @if($activeAuthTab === 'login') active @endif"
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
                                    class="nav-link @if($activeAuthTab === 'register') active @endif"
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
            @endif

            @auth
                <div class="fc-petition-card card border-0 shadow-sm">
                    <div class="card-body p-4">

                        <div class="mb-3">
                            <div class="fw-semibold">{{ __('create.petition_data') }}</div>
                            <div style="height:2px;background:#d61f26;width:100%;margin-top:6px;"></div>
                        </div>

                        <form class="fc-petition-form" data-petition-form method="POST" action="{{ $isEdit
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

                                <div class="fc-markup-hint">{{ __('create.markup_hint') }}</div>
                            </div>

                            <div class="row g-3 mb-3">
                                @unless($isEdit)
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
                                @endunless

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

                            <div class="mb-3" data-image-field>
                                <label class="form-label">{{ __('form.image') }}</label>

                                <p class="text-muted small mb-2" data-image-instructions>
                                    Upload an image or provide an external image link. Use only one option.
                                </p>

                                {{-- Advisory only; the server enforces the 4 MB limit. --}}
                                <input type="hidden" name="MAX_FILE_SIZE" value="4194304">

                                <input type="file" class="form-control @error('image') is-invalid @enderror"
                                    name="image" accept=".jpg,.jpeg,.png,.gif,.webp,image/jpeg,image/png,image/gif,image/webp"
                                    data-image-input data-max-size="4194304">

                                <small class="text-muted d-block mt-1" data-image-formats>
                                    Accepted formats: JPG, PNG, GIF, WebP — max 4 MB.
                                </small>

                                @error('image')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror

                                {{-- Client-side preview (hidden until a file is chosen) --}}
                                <div class="mt-3 d-none" data-image-preview>
                                    <img src="" alt="Selected image preview" class="img-thumbnail"
                                        style="max-height: 220px; width: auto; height: auto; object-fit: contain;" data-image-preview-img>
                                    <div class="d-flex align-items-center justify-content-between mt-2">
                                        <small class="text-muted">
                                            <span data-image-name></span>
                                            <span class="ms-1" data-image-size></span>
                                        </small>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-image-remove>
                                            Remove
                                        </button>
                                    </div>
                                </div>

                                @if($isEdit && ($petition->cover_image || $petition->image_url))
                                    <div class="mt-3">
                                        <img src="{{ $petition->coverUrl() }}" class="img-thumbnail" style="max-height: 220px;" alt="Current petition image">
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove-petition-image">
                                            <label class="form-check-label" for="remove-petition-image">Remove current image</label>
                                        </div>
                                    </div>
                                @endif

                                <div class="fc-or-divider" role="separator" aria-label="or">
                                    <span>OR</span>
                                </div>

                                <div class="mt-2">
                                    <label class="form-label">{{ __('form.image_external') }}</label>
                                    <input class="form-control @error('image_url') is-invalid @enderror"
                                        name="image_url" value="{{ $oldImageUrl }}" placeholder="https://" data-image-url>
                                    @error('image_url')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
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

                            <div class="fc-submit-bar text-center">
                                <button class="btn btn-danger px-5" data-submit-btn
                                    data-submitting-text="{{ $isEdit ? __('create.update') : 'Creating petition…' }}">
                                    {{ $isEdit ? __('create.update') : __('form.submit') }}
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            @endauth

        </div>
    </section>
@endsection

@push('scripts')
    <script src="{{ asset('legacy/js-v2/petition-create.js') }}"></script>
@endpush
