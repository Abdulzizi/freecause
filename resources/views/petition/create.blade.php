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
                                        title="Bold (Ctrl+B)"><b>B</b></button>
                                    <button type="button" class="fc-mbtn" data-cmd="wrap" data-tag="em"
                                        title="Italic (Ctrl+I)"><i>I</i></button>
                                    <button type="button" class="fc-mbtn" data-cmd="wrap" data-tag="u"
                                        title="Underline (Ctrl+U)"><u>U</u></button>

                                    <span class="fc-markup-divider"></span>

                                    <button type="button" class="fc-mbtn" data-cmd="list" data-type="ul"
                                        title="Bullet list">•</button>
                                    <button type="button" class="fc-mbtn" data-cmd="list" data-type="ol"
                                        title="Numbered list">1.</button>

                                    <span class="fc-markup-spacer"></span>
                                </div>

                                <textarea id="petition_description" class="form-control" name="description" rows="10"
                                    required>{{ old('description') }}</textarea>
                            </div>

                            <div class="fc-markup-hint">allowed: br, p, strong, em, u, ul, ol, li</div>

                            <div class="mt-3">
                                <div class="fw-semibold mb-2" style="font-size:14px;">Preview</div>
                                <div id="petition_description_preview" class="fc-preview"></div>
                            </div>
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

    .fc-preview {
        border: 1px solid #e3e3e3;
        border-radius: 4px;
        padding: 12px;
        background: #fff;
        min-height: 120px;
        font-size: 14px;
        line-height: 1.45;
    }

    .fc-preview ul,
    .fc-preview ol {
        margin: 0 0 0 22px;
    }

    .fc-preview p {
        margin: 0 0 10px;
    }

    .fc-preview ul {
        list-style: disc;
        padding-left: 22px;
    }

    .fc-preview ol {
        list-style: decimal;
        padding-left: 22px;
    }

    .fc-preview li {
        display: list-item;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ta = document.getElementById('petition_description');
        const preview = document.getElementById('petition_description_preview');
        if (!ta || !preview) return;

        const ALLOWED = new Set(['BR', 'P', 'STRONG', 'EM', 'U', 'UL', 'OL', 'LI']);

        function sanitizeToPreview(html) {
            const tpl = document.createElement('template');
            tpl.innerHTML = html;

            const walker = document.createTreeWalker(tpl.content, NodeFilter.SHOW_ELEMENT, null);
            const toRemove = [];

            while (walker.nextNode()) {
                const el = walker.currentNode;

                if (!ALLOWED.has(el.tagName)) {
                    toRemove.push(el);
                    continue;
                }

                [...el.attributes].forEach(a => el.removeAttribute(a.name));
            }

            toRemove.forEach(el => {
                const parent = el.parentNode;
                if (!parent) return;

                while (el.firstChild) parent.insertBefore(el.firstChild, el);
                parent.removeChild(el);
            });

            return tpl.innerHTML;
        }

        function updatePreview() {
            const html = ta.value || '';
            preview.innerHTML = sanitizeToPreview(html.replace(/\n/g, '<br>'));
        }

        updatePreview();

        function getSelectionRange() {
            return { start: ta.selectionStart ?? 0, end: ta.selectionEnd ?? 0 };
        }
        function setCursor(pos) { ta.focus(); ta.setSelectionRange(pos, pos); }

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
                updatePreview();
                return;
            }

            ta.value = before + open + selected + close + after;
            setCursor(before.length + open.length + selected.length + close.length);
            updatePreview();
        }

        function getLineBounds(pos) {
            const v = ta.value;
            const lineStart = v.lastIndexOf('\n', pos - 1) + 1;
            const lineEnd = v.indexOf('\n', pos);
            return { lineStart, lineEnd: lineEnd === -1 ? v.length : lineEnd };
        }

        function sanitizeInline(html) {
            const tpl = document.createElement('template');
            tpl.innerHTML = html;

            const allowedInline = new Set(['BR', 'STRONG', 'EM', 'U']);

            const walker = document.createTreeWalker(tpl.content, NodeFilter.SHOW_ELEMENT, null);
            const toRemove = [];

            while (walker.nextNode()) {
                const el = walker.currentNode;

                if (!allowedInline.has(el.tagName)) {
                    toRemove.push(el);
                    continue;
                }

                [...el.attributes].forEach(a => el.removeAttribute(a.name));
            }

            toRemove.forEach(el => {
                const parent = el.parentNode;
                if (!parent) return;
                while (el.firstChild) parent.insertBefore(el.firstChild, el);
                parent.removeChild(el);
            });

            return tpl.innerHTML;
        }


        function makeList(type) {
            const { start, end } = getSelectionRange();
            const v = ta.value;

            let s = start, e = end;
            if (s === e) {
                const b = getLineBounds(s);
                s = b.lineStart; e = b.lineEnd;
            } else {
                const b1 = getLineBounds(s);
                const b2 = getLineBounds(e);
                s = b1.lineStart; e = b2.lineEnd;
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
                x = x.replace(/^\s*[-*]\s+/, '');
                x = x.replace(/^\s*\d+\.\s+/, '');
                return x;
            });

            const tag = type === 'ol' ? 'ol' : 'ul';
            const listHtml =
                `<${tag}>\n` +
                lines.map(l => `  <li>${sanitizeInline(l.trim())}</li>`).join('\n') +
                `\n</${tag}>`;

            ta.value = before + listHtml + after;
            setCursor(before.length + listHtml.length);
            updatePreview();
        }

        document.querySelectorAll('.fc-mbtn').forEach(btn => {
            btn.addEventListener('click', () => {
                const cmd = btn.dataset.cmd;
                if (cmd === 'wrap') return wrapTag(btn.dataset.tag);
                if (cmd === 'list') return makeList(btn.dataset.type);
            });
        });

        ta.addEventListener('input', updatePreview);

        ta.addEventListener('keydown', (e) => {
            if (!(e.ctrlKey || e.metaKey)) return;

            const k = e.key.toLowerCase();
            if (k === 'b') { e.preventDefault(); wrapTag('strong'); }
            if (k === 'i') { e.preventDefault(); wrapTag('em'); }
            if (k === 'u') { e.preventDefault(); wrapTag('u'); }
        });
    });
</script>
