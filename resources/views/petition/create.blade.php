@extends('layouts.legacy')

@section('title', 'Create petition - FreeCause')

@section('content')
    <section class="py-5">
        <div class="container">

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


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

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <div class="mb-3">
                        <div class="fw-semibold">Petition Data</div>
                        <div style="height:2px;background:#d61f26;width:100%;margin-top:6px;"></div>
                    </div>

                    <form method="POST" action="{{ lroute('petition.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Title (mandatory)</label>
                            <input class="form-control" name="title" value="{{ old('title') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Text (mandatory)</label>

                            <div class="fc-markup">
                                <div class="fc-markup-bar">
                                    <button type="button" class="fc-mbtn" data-cmd="wrap" data-tag="strong"
                                        title="Bold"><b>B</b></button>
                                    <button type="button" class="fc-mbtn" data-cmd="wrap" data-tag="em"
                                        title="Italic"><i>I</i></button>
                                    <button type="button" class="fc-mbtn" data-cmd="wrap" data-tag="u"
                                        title="Underline"><u>U</u></button>

                                    <span class="fc-markup-divider"></span>

                                    <button type="button" class="fc-mbtn" data-cmd="list" data-type="ul"
                                        title="Bullet list">•</button>
                                    <button type="button" class="fc-mbtn" data-cmd="list" data-type="ol"
                                        title="Numbered list">1.</button>

                                    <span class="fc-markup-spacer"></span>
                                </div>

                                <textarea id="petition_description"
                                    class="form-control @error('description') is-invalid @enderror" name="description"
                                    rows="10" required>{{ old('description') }}</textarea>
                            </div>

                            <div class="fc-markup-hint">allowed: br, p, strong, em, u, ul, ol, li</div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Goal (mandatory)</label>

                                <select class="form-select" name="goal_signatures" required>
                                    <option value="">(select one)</option>

                                    @php
                                        $goals = [
                                            50,
                                            100,
                                            1000,
                                            5000,
                                            10000,
                                            50000,
                                            100000,
                                            500000,
                                            1000000,
                                            10000000,
                                        ];

                                        $goalLabel = fn($n) => number_format($n, 0, '.', "'") . ' signatures';
                                    @endphp

                                    @foreach ($goals as $g)
                                        <option value="{{ $g }}" @selected((int) old('goal_signatures') === $g)>
                                            {{ $goalLabel($g) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category (mandatory)</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">(select one)</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <input class="form-control" name="tags" value="{{ old('tags') }}">
                            <small class="text-muted">10 keywords max, separated by comma</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image">
                            <div class="mt-2">
                                <label class="form-label">Or supply an external link :</label>
                                <input class="form-control" name="image_url" value="{{ old('image_url') }}"
                                    placeholder="https://">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">URL Youtube video</label>
                            <input class="form-control" name="youtube" value="{{ old('youtube') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Petition target</label>
                            <input class="form-control" name="target" value="{{ old('target') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Petition community</label>
                            <input class="form-control" name="community" value="{{ old('community') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Petition community url</label>
                            <input class="form-control" name="community_url" value="{{ old('community_url') }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">City</label>
                            <input class="form-control" name="city" value="{{ old('city') }}">
                        </div>

                        <div class="text-center">
                            @auth
                                <button class="btn btn-danger px-5">Submit</button>
                            @else
                                <button class="btn btn-danger px-5" disabled title="Please login first">Submit</button>
                                <div class="text-muted mt-2" style="font-size:14px;">Please login or register above to submit.
                                </div>
                            @endauth
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </section>
@endsection

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
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        const ta = document.getElementById('petition_description');
        if (!ta) return;

        function getSelectionRange() {
            return {
                start: ta.selectionStart ?? 0,
                end: ta.selectionEnd ?? 0,
            };
        }

        function setCursor(pos) {
            ta.focus();
            ta.setSelectionRange(pos, pos);
        }

        function wrapTag(tag) {
            const { start, end } = getSelectionRange();
            const before = ta.value.slice(0, start);
            const selected = ta.value.slice(start, end);
            const after = ta.value.slice(end);

            const open = `<${tag}>`;
            const close = `</${tag}>`;

            if (!selected) {
                ta.value = before + open + close + after;
                setCursor(before.length + open.length);
                return;
            }

            ta.value = before + open + selected + close + after;
            setCursor(before.length + open.length + selected.length + close.length);
        }

        function getLineBounds(pos) {
            const v = ta.value;
            const lineStart = v.lastIndexOf('\n', pos - 1) + 1;
            const lineEnd = v.indexOf('\n', pos);
            return { lineStart, lineEnd: lineEnd === -1 ? v.length : lineEnd };
        }

        function makeList(type) {
            const { start, end } = getSelectionRange();
            const v = ta.value;

            let s = start, e = end;
            if (s === e) {
                const b = getLineBounds(s);
                s = b.lineStart;
                e = b.lineEnd;
            } else {
                const b1 = getLineBounds(s);
                const b2 = getLineBounds(e);
                s = b1.lineStart;
                e = b2.lineEnd;
            }

            const before = v.slice(0, s);
            const block = v.slice(s, e);
            const after = v.slice(e);

            let lines = block.split('\n');

            while (lines.length && lines[0].trim() === '') lines.shift();
            while (lines.length && lines[lines.length - 1].trim() === '') lines.pop();

            if (!lines.length) return;

            lines = lines.map(line => {
                let x = line.trimEnd();
                x = x.replace(/^\s*[-*]\s+/, '');      // bullets
                x = x.replace(/^\s*\d+\.\s+/, '');     // numbered
                return x;
            });

            const tag = type === 'ol' ? 'ol' : 'ul';
            const listHtml =
                `<${tag}>\n` +
                lines.map(l => `  <li>${escapeHtmlLoose(l.trim())}</li>`).join('\n') +
                `\n</${tag}>`;

            ta.value = before + listHtml + after;

            const newPos = before.length + listHtml.length;
            setCursor(newPos);
        }

        function escapeHtmlLoose(s) {
            return s
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;');
        }

        document.querySelectorAll('.fc-mbtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const cmd = btn.dataset.cmd;

                if (cmd === 'wrap') {
                    wrapTag(btn.dataset.tag);
                    return;
                }

                if (cmd === 'list') {
                    makeList(btn.dataset.type);
                    return;
                }
            });
        });
    });
</script>
