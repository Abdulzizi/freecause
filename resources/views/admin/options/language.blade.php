@extends('admin.layouts.app')

@section('title', 'Localized Content')

@section('content')

    <h1>localized content</h1>

    <div class="fc-tab">choose language</div>
    <div class="fc-box">
        <form method="get" action="{{ route('admin.options.language') }}">
            <select class="fc-select" name="locale" onchange="this.form.submit()">
                <option value="">select language</option>
                @foreach($locales as $code => $name)
                    <option value="{{ $code }}" {{ $locale === $code ? 'selected' : '' }}>
                        {{ strtoupper($code) }} - {{ $name }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    @if($showForm)

        <form method="post" action="{{ route('admin.options.language.update') }}">
            @csrf
            <input type="hidden" name="locale" value="{{ $locale }}">

            {{-- head --}}
            <div class="fc-tab">&lt;head&gt;</div>
            <div class="fc-box">

                <div class="fc-row">
                    <label>meta keywords</label>
                    <textarea class="fc-textarea" name="meta_keywords">{{ $values['meta_keywords'] ?? '' }}</textarea>
                </div>

                <div class="fc-row">
                    <label>meta description</label>
                    <textarea class="fc-textarea" name="meta_description">{{ $values['meta_description'] ?? '' }}</textarea>
                </div>

            </div>

            <div class="fc-tab">main content</div>
            <div class="fc-box">

                <div class="fc-row">
                    <label>h1</label>
                    <textarea class="fc-textarea" name="h1">{{ $values['h1'] ?? '' }}</textarea>
                </div>

                <div class="fc-row">
                    <label>h2 (html allowed)</label>
                    <textarea class="fc-textarea" name="h2">{{ $values['h2'] ?? '' }}</textarea>
                </div>

                <div class="fc-row">
                    <label>text index left (full html)</label>
                    <textarea class="fc-textarea fc-editor"
                        name="text_index_left">{{ $values['text_index_left'] ?? '' }}</textarea>
                </div>

                <div class="fc-row">
                    <label>text index right (full html)</label>
                    <textarea class="fc-textarea fc-editor"
                        name="text_index_right">{{ $values['text_index_right'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="fc-tab">footer</div>
            <div class="fc-box">

                <div class="fc-row">
                    <label>footer about (html allowed)</label>
                    <textarea class="fc-textarea fc-editor"
                        name="footer_about">{{ $layoutValues['footer_about'] ?? '' }}</textarea>
                </div>

                <div class="fc-row">
                    <label>footer links (tokens allowed)</label>
                    <textarea class="fc-textarea"
                        name="footer_links">{{ $layoutValues['footer_links'] ?? '' }}</textarea>
                </div>

                <div class="fc-row">
                    <label>footer bottom (tokens allowed)</label>
                    <textarea class="fc-textarea"
                        name="footer_bottom">{{ $layoutValues['footer_bottom'] ?? '' }}</textarea>
                </div>

            </div>

            <div class="fc-tab">extra</div>
            <div class="fc-box">
                <div class="fc-row">
                    <label>exclude petitions from most read (id1,id2,...)</label>
                    <input class="fc-input" type="text" name="exclude_most_read"
                        value="{{ $values['exclude_most_read'] ?? '' }}">
                </div>
            </div>

            @if (!empty($is_system_admin))
            <div class="fc-tab">advanced localized HTML</div>
            <div class="fc-box">
                <div class="fc-row">
                    <label>additional head html</label>
                    <textarea class="fc-textarea"
                        name="head_additional_html">{{ $values['head_additional_html'] ?? '' }}</textarea>
                </div>
            </div>
            @endif

            <div style="text-align:right; margin-top:10px;">
                <button class="fc-btn" type="submit">save</button>
            </div>

        </form>

    @endif

@endsection
