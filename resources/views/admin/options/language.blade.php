@extends('admin.layouts.app')

@section('title', 'Localized Content')

@section('content')

    <x-admin.page-header title="localized content" />

    <x-admin.section-panel title="choose language">
        <form method="get" action="{{ route('admin.options.language') }}">
            <select class="fc-filter-field" name="locale" aria-label="language" data-admin-autosubmit>
                <option value="">select language</option>
                @foreach($locales as $code => $name)
                    <option value="{{ $code }}" {{ $locale === $code ? 'selected' : '' }}>
                        {{ strtoupper($code) }} - {{ $name }}
                    </option>
                @endforeach
            </select>
        </form>
    </x-admin.section-panel>

    @if ($is_system_admin)
        <x-admin.section-panel title="automatic email translation">
            <p class="text-sm text-muted">Connect your free DeepL API Developer account. Only template text is translated; recipient details and personal links are never submitted. Existing translations remain available if the service is unavailable or its quota is exhausted.</p>
            <form method="post" action="{{ route('admin.options.language.translationKey') }}" data-turbo="false">
                @csrf
                <input type="hidden" name="locale" value="{{ $locale }}">
                <x-admin.form-field label="DeepL API key">
                    <x-blatui.input type="password" name="deepl_api_key" value="" autocomplete="new-password" :placeholder="$translationConfigured ? 'Configured — enter a key only to replace it' : 'Paste your DeepL API key'" required />
                </x-admin.form-field>
                <x-blatui.button type="submit">Save translation service</x-blatui.button>
            </form>
            @foreach ($emailSyncStatus as $code => $sync)
                @if ($sync)
                    <p class="text-sm text-muted">{{ strtoupper($code) }}: {{ $sync['state'] ?? '' }} {{ $sync['message'] ?? '' }}</p>
                @endif
            @endforeach
        </x-admin.section-panel>
    @endif

    @if($showForm)

        <form method="post" action="{{ route('admin.options.language.update') }}" data-turbo="false">
            @csrf
            <input type="hidden" name="locale" value="{{ $locale }}">

            <x-admin.section-panel :title="html_entity_decode('&lt;head&gt;')">

                <x-admin.form-field label="meta keywords">
                    <textarea class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono" name="meta_keywords">{{ $values['meta_keywords'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="meta description">
                    <textarea class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono" name="meta_description">{{ $values['meta_description'] ?? '' }}</textarea>
                </x-admin.form-field>

            </x-admin.section-panel>

            <x-admin.section-panel title="main content">

                <x-admin.form-field label="h1">
                    <textarea class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono" name="h1">{{ $values['h1'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="h2 (html allowed)">
                    <textarea class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono" name="h2">{{ $values['h2'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="text index left (full html)">
                    <textarea class="fc-editor w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono"
                        name="text_index_left">{{ $values['text_index_left'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="text index right (full html)">
                    <textarea class="fc-editor w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono"
                        name="text_index_right">{{ $values['text_index_right'] ?? '' }}</textarea>
                </x-admin.form-field>
            </x-admin.section-panel>

            <x-admin.section-panel title="email templates">
                <p class="text-sm text-muted">System email defaults for this language. These texts are used when the recipient has selected this language. Keep placeholders such as :name, :title and :count unchanged.</p>
                @foreach ($emailValues as $emailKey => $emailValue)
                    <x-admin.form-field :label="str_replace('_', ' ', $emailKey)">
                        <textarea class="fc-filter-field w-full" name="email_templates[{{ $emailKey }}]" rows="3" required>{{ old('email_templates.'.$emailKey, $emailValue) }}</textarea>
                        @error('email_templates.'.$emailKey)
                            <p class="text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </x-admin.form-field>
                @endforeach
            </x-admin.section-panel>

            <x-admin.section-panel title="footer">

                <x-admin.form-field label="footer about (html allowed)">
                    <textarea class="fc-editor w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono"
                        name="footer_about">{{ $layoutValues['footer_about'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="footer links (tokens allowed)">
                    <textarea class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono"
                        name="footer_links">{{ $layoutValues['footer_links'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="footer bottom (tokens allowed)">
                    <textarea class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono"
                        name="footer_bottom">{{ $layoutValues['footer_bottom'] ?? '' }}</textarea>
                </x-admin.form-field>

            </x-admin.section-panel>

            <x-admin.section-panel title="extra">
                <x-admin.form-field label="exclude petitions from most read (id1,id2,...)">
                    <x-ui.input name="exclude_most_read" :value="$values['exclude_most_read'] ?? ''" class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white" />
                </x-admin.form-field>
            </x-admin.section-panel>

            @if (!empty($is_system_admin))
            <x-admin.section-panel title="advanced localized HTML">
                <x-admin.form-field label="additional head html">
                    <textarea class="w-full box-border border border-border rounded-sm px-2 py-1.5 text-sm bg-white min-h-[140px] resize-y font-mono"
                        name="head_additional_html">{{ $values['head_additional_html'] ?? '' }}</textarea>
                </x-admin.form-field>
            </x-admin.section-panel>
            @endif

            @if ($is_system_admin)
                <div class="text-right mt-2.5">
                    <x-blatui.button type="submit" name="translate_all" value="1" :disabled="! $translationConfigured">Save, translate and sync all email languages</x-blatui.button>
                </div>
            @endif
            <div class="text-right mt-2.5">
                <x-blatui.button type="submit">save</x-blatui.button>
            </div>

        </form>

    @endif

@endsection
