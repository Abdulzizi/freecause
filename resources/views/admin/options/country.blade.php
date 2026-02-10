@extends('admin.layouts.app')

@section('title', 'Country Options')

@section('content')
    <h1>options</h1>

    <div class="fc-tab">choose country</div>
    <div class="fc-box">
        <div style="display:flex; align-items:center; gap:10px;">
            <a class="fc-btn"
                href="{{ $locale ? route('admin.options.country', ['locale' => $prev]) : 'javascript:void(0)' }}"
                style="text-decoration:none; {{ $locale ? '' : 'opacity:.5; pointer-events:none;' }}">
                &lt;--
            </a>

            <form method="get" action="{{ route('admin.options.country') }}" style="margin:0;">
                <select class="fc-select" name="locale" onchange="this.form.submit()" style="max-width:340px;">
                    <option value="">Select Country</option>
                    @foreach($locales as $code => $name)
                        <option value="{{ $code }}" {{ $locale === $code ? 'selected' : '' }}>
                            {{ $code }} - {{ $name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <a class="fc-btn"
                href="{{ $locale ? route('admin.options.country', ['locale' => $next]) : 'javascript:void(0)' }}"
                style="text-decoration:none; {{ $locale ? '' : 'opacity:.5; pointer-events:none;' }}">
                --&gt;
            </a>

            <span style="color:#999; font-style:italic;">
                switching will loose changes if not saved manually!
            </span>
        </div>
    </div>

    @if($showForm)
        <div class="fc-tab">options</div>
        <div class="fc-box">

            <form method="post" action="{{ route('admin.options.country.update') }}">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">

                <div class="fc-tab" style="margin-top:0;">&lt;head&gt;</div>
                <div class="fc-box">
                    <div class="fc-row">
                        <label>keywords</label>
                        <textarea class="fc-textarea" name="meta_keywords">{{ $values['meta_keywords'] }}</textarea>
                    </div>

                    <div class="fc-row">
                        <label>description</label>
                        <textarea class="fc-textarea" name="meta_description">{{ $values['meta_description'] }}</textarea>
                    </div>

                    <div class="fc-row">
                        <label>additional html</label>
                        <textarea class="fc-textarea"
                            name="head_additional_html">{{ $values['head_additional_html'] }}</textarea>
                    </div>
                </div>

                <div class="fc-tab">misc</div>
                <div class="fc-box">
                    <div class="fc-row">
                        <label>locale</label>
                        <input class="fc-input" type="text" value="{{ $locale }}" disabled style="max-width:120px;">
                    </div>

                    <div class="fc-row">
                        <label>h1</label>
                        <textarea class="fc-textarea" name="h1">{{ $values['h1'] }}</textarea>
                    </div>

                    <div class="fc-row">
                        <label>h2</label>
                        <textarea class="fc-textarea" name="h2">{{ $values['h2'] }}</textarea>
                    </div>

                    <div class="fc-row">
                        <label>text index left</label>
                        <textarea class="fc-textarea" name="text_index_left">{{ $values['text_index_left'] }}</textarea>
                    </div>

                    <div class="fc-row">
                        <label>text index right</label>
                        <textarea class="fc-textarea" name="text_index_right">{{ $values['text_index_right'] }}</textarea>
                    </div>

                    <div class="fc-row">
                        <label>footer</label>
                        <textarea class="fc-textarea" name="footer">{{ $values['footer'] }}</textarea>
                    </div>

                    <div class="fc-row">
                        <label>exclude petitions from most read (id1,id2,...)</label>
                        <input class="fc-input" type="text" name="exclude_most_read" value="{{ $values['exclude_most_read'] }}">
                    </div>
                </div>

                <div style="display:flex; justify-content:flex-end; margin-top:6px;">
                    <button class="fc-btn" type="submit" title="save">save</button>
                </div>
            </form>
        </div>
    @endif
@endsection
