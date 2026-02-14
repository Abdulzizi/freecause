@extends('admin.layouts.app')

@section('title', 'Homepage Content')

@section('content')

    <h1>homepage content</h1>

    {{-- LANGUAGE SELECTOR --}}
    <div class="fc-tab">choose language</div>
    <div class="fc-box">
        <form method="get" action="{{ route('admin.options.language') }}"
            style="display:flex; gap:10px; align-items:center;">
            <select class="fc-select" name="locale" onchange="this.form.submit()" style="max-width:300px;">
                <option value="">Select Language</option>
                @foreach($locales as $code => $name)
                    <option value="{{ $code }}" {{ $locale === $code ? 'selected' : '' }}>
                        {{ strtoupper($code) }} - {{ $name }}
                    </option>
                @endforeach
            </select>

            <span style="color:#999; font-style:italic;">
                switching will lose unsaved changes
            </span>
        </form>
    </div>

    @if($showForm)
        <form method="post" action="{{ route('admin.options.language.update') }}">
            @csrf
            <input type="hidden" name="locale" value="{{ $locale }}">

            {{-- ================= HERO ================= --}}
            <div class="fc-tab">hero section</div>
            <div class="fc-box">
                <div class="fc-row">
                    <label>hero h1</label>
                    <textarea class="fc-textarea" name="hero_h1">{{ $values['hero_h1'] ?? '' }}</textarea>
                </div>

                <div class="fc-row">
                    <label>hero h2 (html allowed)</label>
                    <textarea class="fc-textarea" name="hero_h2">{{ $values['hero_h2'] ?? '' }}</textarea>
                </div>

                <div class="fc-row">
                    <label>create petition button</label>
                    <input class="fc-input" type="text" name="btn_create_petition"
                        value="{{ $values['btn_create_petition'] ?? '' }}">
                </div>
            </div>

            {{-- ================= TABS ================= --}}
            <div class="fc-tab">tabs</div>
            <div class="fc-box">
                <div class="fc-row">
                    <label>featured tab label</label>
                    <input class="fc-input" type="text" name="tab_featured" value="{{ $values['tab_featured'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>recent tab label</label>
                    <input class="fc-input" type="text" name="tab_recent" value="{{ $values['tab_recent'] ?? '' }}">
                </div>
            </div>

            {{-- ================= FEATURED BOX ================= --}}
            <div class="fc-tab">featured box</div>
            <div class="fc-box">

                <div class="fc-row">
                    <label>badge label</label>
                    <input class="fc-input" type="text" name="featured_badge" value="{{ $values['featured_badge'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>empty title</label>
                    <input class="fc-input" type="text" name="featured_none_title"
                        value="{{ $values['featured_none_title'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>empty subtitle</label>
                    <input class="fc-input" type="text" name="featured_none_sub"
                        value="{{ $values['featured_none_sub'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>read more label</label>
                    <input class="fc-input" type="text" name="read_more" value="{{ $values['read_more'] ?? '' }}">
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
            </div>

            {{-- ================= RECENT ================= --}}
            <div class="fc-tab">recent activity</div>
            <div class="fc-box">
                <div class="fc-row">
                    <label>has signed text</label>
                    <input class="fc-input" type="text" name="recent_has_signed"
                        value="{{ $values['recent_has_signed'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>empty message</label>
                    <input class="fc-input" type="text" name="recent_empty" value="{{ $values['recent_empty'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>petition fallback label</label>
                    <input class="fc-input" type="text" name="petition_fallback"
                        value="{{ $values['petition_fallback'] ?? '' }}">
                </div>
            </div>

            {{-- ================= WHAT SECTION ================= --}}
            <div class="fc-tab">what is online petition</div>
            <div class="fc-box">

                <div class="fc-row">
                    <label>section title</label>
                    <input class="fc-input" type="text" name="what_title" value="{{ $values['what_title'] ?? '' }}">
                </div>

                @for($i = 1; $i <= 4; $i++)
                    <div class="fc-row">
                        <label>paragraph {{ $i }} (html allowed)</label>
                        <textarea class="fc-textarea" name="what_p{{ $i }}">{{ $values["what_p{$i}"] ?? '' }}</textarea>
                    </div>
                @endfor

                <div class="fc-row">
                    <label>learn more link text</label>
                    <input class="fc-input" type="text" name="what_link" value="{{ $values['what_link'] ?? '' }}">
                </div>

            </div>

            {{-- ================= CREATE BOX ================= --}}
            <div class="fc-tab">create petition box</div>
            <div class="fc-box">

                <div class="fc-row">
                    <label>box title</label>
                    <input class="fc-input" type="text" name="create_box_title" value="{{ $values['create_box_title'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>intro text</label>
                    <textarea class="fc-textarea" name="create_box_p1">{{ $values['create_box_p1'] ?? '' }}</textarea>
                </div>

                @for($i = 1; $i <= 6; $i++)
                    <div class="fc-row">
                        <label>list item {{ $i }}</label>
                        <input class="fc-input" type="text" name="create_box_li{{ $i }}"
                            value="{{ $values["create_box_li{$i}"] ?? '' }}">
                    </div>
                @endfor

                <div class="fc-row">
                    <label>closing text</label>
                    <textarea class="fc-textarea" name="create_box_p2">{{ $values['create_box_p2'] ?? '' }}</textarea>
                </div>

                <div class="fc-row">
                    <label>cta link text</label>
                    <input class="fc-input" type="text" name="create_box_link" value="{{ $values['create_box_link'] ?? '' }}">
                </div>

            </div>

            {{-- ================= CATEGORIES + BLOG ================= --}}
            <div class="fc-tab">categories & blog</div>
            <div class="fc-box">

                <div class="fc-row">
                    <label>categories title</label>
                    <input class="fc-input" type="text" name="categories_title" value="{{ $values['categories_title'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>blog title</label>
                    <input class="fc-input" type="text" name="blog_title" value="{{ $values['blog_title'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>blog subtitle</label>
                    <input class="fc-input" type="text" name="blog_subtitle" value="{{ $values['blog_subtitle'] ?? '' }}">
                </div>

                <div class="fc-row">
                    <label>blog read more label</label>
                    <input class="fc-input" type="text" name="blog_read_more" value="{{ $values['blog_read_more'] ?? '' }}">
                </div>

            </div>

            <div style="display:flex; justify-content:flex-end; margin-top:8px;">
                <button class="fc-btn" type="submit">save</button>
            </div>

        </form>

    @endif

@endsection
