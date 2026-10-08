@php
    use App\Models\Language;

    $languages = cache()->remember('active_languages_full', 60, fn() => Language::where('is_active', 1)->orderByDesc('is_default')->get(),);
    $locale = app()->getLocale();
    $current = $languages->firstWhere('code', $locale) ?? $languages->first();
@endphp

<div class="dropdown static!">
    <button class="btn dropdown-toggle p-0! border-0! bg-transparent! after:hidden!" type="button" data-bs-toggle="dropdown" aria-expanded="false">
        @if ($current?->flag)
            <img class="block! w-[34px]! h-[34px]! rounded-full!" src="{{ asset($current->flag) }}" alt="{{ $current->name }}">
        @endif
    </button>

    <div class="dropdown-menu w-full! shadow-sm! border-0! mt-2! rounded-xl! start-0! end-0! top-full! min-w-0! m-0! block! opacity-0! -translate-y-2.5! pointer-events-none! transition-[opacity,transform]! duration-[220ms]! ease-[cubic-bezier(0.4,0,0.2,1)]! [&.show]:opacity-100! [&.show]:translate-y-0! [&.show]:pointer-events-auto!">
        <x-ui.container class="py-4!">
            <div class="flex! flex-wrap! gap-2!">
                @foreach ($languages as $lang)
                    <a class="flex! items-center! gap-2! px-[10px]! py-[6px]! rounded-[10px]! bg-white! border! border-[#eee]! text-sm! text-[#cc1f1f]! focus-visible:outline-none! focus-visible:ring-2! focus-visible:ring-[#cc1f1f]! {{ $lang->code === $locale ? 'border-[#cc1f1f]! bg-[#fff5f5]!' : '' }}"
                        href="{{ locale_url($lang->code) }}"
                        data-no-progress
                        @if ($lang->code === $locale) aria-current="true" @endif>
                        @if ($lang->flag)
                            <img class="w-[22px]! h-[22px]! rounded-full!" src="{{ asset($lang->flag) }}" width="20" alt="{{ $lang->name }}">
                        @endif
                        <span>{{ $lang->name }}</span>
                    </a>
                @endforeach
            </div>
        </x-ui.container>
    </div>
</div>
