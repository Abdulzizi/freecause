@extends('admin.layouts.app')

@section('title', 'Localized Content')

@section('content')

    <x-admin.page-header title="localized content" />

    <x-admin.section-panel title="choose language">
        <form method="get" action="{{ route('admin.options.language') }}">
            <select class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="locale" data-admin-autosubmit>
                <option value="">select language</option>
                @foreach($locales as $code => $name)
                    <option value="{{ $code }}" {{ $locale === $code ? 'selected' : '' }}>
                        {{ strtoupper($code) }} - {{ $name }}
                    </option>
                @endforeach
            </select>
        </form>
    </x-admin.section-panel>

    @if($showForm)

        <form method="post" action="{{ route('admin.options.language.update') }}">
            @csrf
            <input type="hidden" name="locale" value="{{ $locale }}">

            {{-- head --}}
            <x-admin.section-panel :title="html_entity_decode('&lt;head&gt;')">

                <x-admin.form-field label="meta keywords">
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!" name="meta_keywords">{{ $values['meta_keywords'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="meta description">
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!" name="meta_description">{{ $values['meta_description'] ?? '' }}</textarea>
                </x-admin.form-field>

            </x-admin.section-panel>

            <x-admin.section-panel title="main content">

                <x-admin.form-field label="h1">
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!" name="h1">{{ $values['h1'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="h2 (html allowed)">
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!" name="h2">{{ $values['h2'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="text index left (full html)">
                    <textarea class="fc-editor w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="text_index_left">{{ $values['text_index_left'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="text index right (full html)">
                    <textarea class="fc-editor w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="text_index_right">{{ $values['text_index_right'] ?? '' }}</textarea>
                </x-admin.form-field>
            </x-admin.section-panel>

            <x-admin.section-panel title="footer">

                <x-admin.form-field label="footer about (html allowed)">
                    <textarea class="fc-editor w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="footer_about">{{ $layoutValues['footer_about'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="footer links (tokens allowed)">
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="footer_links">{{ $layoutValues['footer_links'] ?? '' }}</textarea>
                </x-admin.form-field>

                <x-admin.form-field label="footer bottom (tokens allowed)">
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="footer_bottom">{{ $layoutValues['footer_bottom'] ?? '' }}</textarea>
                </x-admin.form-field>

            </x-admin.section-panel>

            <x-admin.section-panel title="extra">
                <x-admin.form-field label="exclude petitions from most read (id1,id2,...)">
                    <x-ui.input name="exclude_most_read" :value="$values['exclude_most_read'] ?? ''" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" />
                </x-admin.form-field>
            </x-admin.section-panel>

            @if (!empty($is_system_admin))
            <x-admin.section-panel title="advanced localized HTML">
                <x-admin.form-field label="additional head html">
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="head_additional_html">{{ $values['head_additional_html'] ?? '' }}</textarea>
                </x-admin.form-field>
            </x-admin.section-panel>
            @endif

            <div class="text-right! mt-2.5!">
                <x-ui.button variant="secondary" size="sm" type="submit">save</x-ui.button>
            </div>

        </form>

    @endif

@endsection
