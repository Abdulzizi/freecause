@extends('admin.layouts.app')

@section('title', 'Homepage Content')

@section('content')

    <h1>homepage content</h1>

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

                <div class="fc-row">
                    <label>additional html</label>
                    <textarea class="fc-textarea"
                        name="head_additional_html">{{ $values['head_additional_html'] ?? '' }}</textarea>
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

            <div class="fc-tab">featured & tabs</div>
            <div class="fc-box">

                <div class="fc-row">
                    <label>button create petition</label>
                    <input class="fc-input" type="text" name="btn_create_petition"
                        value="{{ $values['btn_create_petition'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>tab featured</label>
                    <input class="fc-input" type="text" name="tab_featured" value="{{ $values['tab_featured'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>tab recent</label>
                    <input class="fc-input" type="text" name="tab_recent" value="{{ $values['tab_recent'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>featured badge</label>
                    <input class="fc-input" type="text" name="featured_badge" value="{{ $values['featured_badge'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>featured none title</label>
                    <input class="fc-input" type="text" name="featured_none_title"
                        value="{{ $values['featured_none_title'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>featured none subtitle</label>
                    <input class="fc-input" type="text" name="featured_none_sub"
                        value="{{ $values['featured_none_sub'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>petition target label</label>
                    <input class="fc-input" type="text" name="petition_target_label"
                        value="{{ $values['petition_target_label'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>signatures label</label>
                    <input class="fc-input" type="text" name="signatures_label" value="{{ $values['signatures_label'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>goal label</label>
                    <input class="fc-input" type="text" name="goal_label" value="{{ $values['goal_label'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>read more label</label>
                    <input class="fc-input" type="text" name="read_more" value="{{ $values['read_more'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>recent has signed</label>
                    <input class="fc-input" type="text" name="recent_has_signed"
                        value="{{ $values['recent_has_signed'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>recent empty</label>
                    <input class="fc-input" type="text" name="recent_empty" value="{{ $values['recent_empty'] ?? '' }}">
                </div>

            </div>

            <div style="text-align:right; margin-top:10px;">
                <button class="fc-btn" type="submit">save</button>
            </div>

        </form>

    @endif

@endsection
