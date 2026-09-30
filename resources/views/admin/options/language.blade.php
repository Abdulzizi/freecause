@extends('admin.layouts.app')

@section('title', 'Localized Content')

@section('content')

    <h1 class="m-0! mb-2! text-lg!">localized content</h1>

    <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">choose language</div>
    <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">
        <form method="get" action="{{ route('admin.options.language') }}">
            <select class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="locale" onchange="this.form.submit()">
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
            <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">&lt;head&gt;</div>
            <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">meta keywords</label>
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!" name="meta_keywords">{{ $values['meta_keywords'] ?? '' }}</textarea>
                </div>

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">meta description</label>
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!" name="meta_description">{{ $values['meta_description'] ?? '' }}</textarea>
                </div>

            </div>

            <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">main content</div>
            <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">h1</label>
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!" name="h1">{{ $values['h1'] ?? '' }}</textarea>
                </div>

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">h2 (html allowed)</label>
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!" name="h2">{{ $values['h2'] ?? '' }}</textarea>
                </div>

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">text index left (full html)</label>
                    <textarea class="fc-editor w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="text_index_left">{{ $values['text_index_left'] ?? '' }}</textarea>
                </div>

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">text index right (full html)</label>
                    <textarea class="fc-editor w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="text_index_right">{{ $values['text_index_right'] ?? '' }}</textarea>
                </div>
            </div>

            <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">footer</div>
            <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">footer about (html allowed)</label>
                    <textarea class="fc-editor w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="footer_about">{{ $layoutValues['footer_about'] ?? '' }}</textarea>
                </div>

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">footer links (tokens allowed)</label>
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="footer_links">{{ $layoutValues['footer_links'] ?? '' }}</textarea>
                </div>

                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">footer bottom (tokens allowed)</label>
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="footer_bottom">{{ $layoutValues['footer_bottom'] ?? '' }}</textarea>
                </div>

            </div>

            <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">extra</div>
            <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">
                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">exclude petitions from most read (id1,id2,...)</label>
                    <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" type="text" name="exclude_most_read"
                        value="{{ $values['exclude_most_read'] ?? '' }}">
                </div>
            </div>

            @if (!empty($is_system_admin))
            <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">advanced localized HTML</div>
            <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">
                <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                    <label class="font-bold! text-sm!">additional head html</label>
                    <textarea class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! min-h-[140px]! resize-y! font-[Consolas,_Courier_New,_monospace]! text-[13px]!"
                        name="head_additional_html">{{ $values['head_additional_html'] ?? '' }}</textarea>
                </div>
            </div>
            @endif

            <div class="text-right! mt-2.5!">
                <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-3! py-1.5! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! text-xs! md:py-1! md:px-2.5! md:text-sm!" type="submit">save</button>
            </div>

        </form>

    @endif

@endsection
