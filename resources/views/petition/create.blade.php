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

@section('title', $isEdit ? 'Edit petition - FreeCause' : 'Create petition - FreeCause')

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
                <h2 class="text-center mb-4">Start a free petition In Just 3 Easy Steps</h2>

                <div class="fc-steps mb-5">
                    <div class="fc-step">
                        <span class="fc-step-icon fc-step-1 is-active"></span>
                        <div class="fc-step-text">Create your petition</div>
                    </div>

                    <div class="fc-step">
                        <span class="fc-step-icon fc-step-2"></span>
                        <div class="fc-step-text">Share with friends</div>
                    </div>

                    <div class="fc-step">
                        <span class="fc-step-icon fc-step-3"></span>
                        <div class="fc-step-text">Change the world!</div>
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
                        <div class="fw-semibold">Petition Data</div>
                        <div style="height:2px;background:#d61f26;width:100%;margin-top:6px;"></div>
                    </div>

                    <form method="POST" action="{{ $isEdit
                        ? lroute('petition.update', ['slug' => $tr->slug, 'id' => $petition->id])
                        : lroute('petition.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Title (mandatory)</label>
                            <input class="form-control" name="title" value="{{ $oldTitle }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Text (mandatory)</label>

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
                                <label class="form-label">Goal (mandatory)</label>

                                <select class="form-select" name="goal_signatures" required>
                                    <option value="">(select one)</option>

                                    @php
                                        $goals = [50, 100, 1000, 5000, 10000, 50000, 100000, 500000, 1000000, 10000000];
                                        $goalLabel = fn($n) => number_format($n, 0, '.', "'") . ' signatures';
                                    @endphp

                                    @foreach ($goals as $g)
                                        <option value="{{ $g }}" @selected((int) $oldGoal === (int) $g)>
                                            {{ $goalLabel($g) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Category (mandatory)</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">(select one)</option>
                                    @foreach ($categories as $c)
                                        <option value="{{ $c->id }}" @selected((int) $oldCategory === (int) $c->id)>
                                            {{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <input class="form-control" name="tags" value="{{ $oldTags }}">
                            <small class="text-muted">10 keywords max, separated by comma</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image">
                            <div class="mt-2">
                                <label class="form-label">Or supply an external link :</label>
                                <input class="form-control" name="image_url" value="{{ $oldImageUrl }}"
                                    placeholder="https://">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">URL Youtube video</label>
                            <input class="form-control" name="youtube" value="{{ $oldYoutube }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Petition target</label>
                            <input class="form-control" name="target" value="{{ $oldTarget }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Petition community</label>
                            <input class="form-control" name="community" value="{{ $oldCommunity }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Petition community url</label>
                            <input class="form-control" name="community_url" value="{{ $oldCommunityUrl }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">City</label>
                            <input class="form-control" name="city" value="{{ $oldCity }}">
                        </div>

                        <div class="text-center">
                            @auth
                                <button class="btn btn-danger px-5">{{ $isEdit ? 'Update' : 'Submit' }}</button>
                            @else
                                <button class="btn btn-danger px-5" disabled title="Please login first">Submit</button>
                                <div class="text-muted mt-2" style="font-size:14px;">
                                    Please login or register above to submit.
                                </div>
                            @endauth
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </section>
@endsection

@if(!$isEdit)
    <style>
        .fc-steps {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 60px;
            flex-wrap: wrap;
            margin-bottom: 2.5rem;
        }

        .fc-step {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 240px;
        }

        .fc-step-text {
            font-size: 20px;
            font-weight: 500;
            color: #111;
            white-space: nowrap;
        }

        .fc-step-icon {
            width: 64px;
            height: 64px;
            display: inline-block;
            background-image: url('{{ asset("legacy/images/startpetition-icons.png") }}');
            background-repeat: no-repeat;
            background-size: 200% 300%;
        }

        .fc-step-1 {
            background-position: 0% 0%;
        }

        .fc-step-2 {
            background-position: 0% 50%;
        }

        .fc-step-3 {
            background-position: 0% 100%;
        }

        .fc-step-icon.is-active.fc-step-1 {
            background-position: 100% 0%;
        }

        .fc-step-icon.is-active.fc-step-2 {
            background-position: 100% 50%;
        }

        .fc-step-icon.is-active.fc-step-3 {
            background-position: 100% 100%;
        }
    </style>
@endif

<style>
    .fc-markup {
        border: 1px solid #d9d9d9;
        border-radius: 4px;
        overflow: hidden;
    }

    .fc-markup-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        padding: 6px;
        background: #f3f3f3;
        border-bottom: 1px solid #d9d9d9;
    }

    .fc-mbtn {
        width: 28px;
        height: 26px;
        border: 1px solid #cfcfcf;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .fc-mbtn:hover {
        background: #f8f8f8;
    }

    .fc-markup-spacer {
        flex: 1;
    }

    .fc-markup textarea {
        border: 0;
        border-radius: 0;
    }

    .fc-markup-hint {
        padding: 8px 2px 0;
        font-size: 13px;
        color: #555;
    }

    .fc-markup-divider {
        width: 1px;
        height: 20px;
        background: #cfcfcf;
        margin: 0 6px;
    }

    .fc-editor {
        min-height: 260px;
        padding: 12px;
        background: #fff;
        font-size: 14px;
        line-height: 1.45;
        outline: none;
    }

    .fc-quill .ql-container {
        font-size: 14px;
        line-height: 1.45;
    }

    .fc-quill .ql-editor {
        min-height: 260px;
    }

    .fc-quill .ql-editor ul {
        list-style: disc !important;
        padding-left: 22px !important;
    }

    .fc-quill .ql-editor ol {
        list-style: decimal !important;
        padding-left: 22px !important;
    }

    .fc-quill .ql-editor li {
        display: list-item !important;
    }
</style>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const editorEl = document.getElementById('petition_editor');
            const hidden = document.getElementById('petition_description');
            if (!editorEl || !hidden || typeof Quill === 'undefined') return;

            const quill = new Quill(editorEl, {
                theme: 'snow',
                modules: {
                    toolbar: '#fc-quill-toolbar',
                    clipboard: { matchVisual: false }
                }
            });

            const initialHtml = (hidden.value || '').trim();
            if (initialHtml) {
                quill.clipboard.dangerouslyPasteHTML(initialHtml);
            }

            function syncHidden() {
                hidden.value = quill.root.innerHTML;
            }

            quill.on('text-change', syncHidden);
            syncHidden();

            const form = editorEl.closest('form');
            if (form) form.addEventListener('submit', syncHidden);
        });
    </script>
@endpush
