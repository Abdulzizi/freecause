@extends('admin.layouts.app')

@section('title', 'Languages')

@section('content')

    <h1 class="m-0! mb-2! text-lg!">languages</h1>

    <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">add language</div>
    <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">
        <form method="post" action="{{ route('admin.languages.store') }}">
            @csrf

            <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm! w-[120px]!">code</label>
                <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="code" placeholder="e.g. es" required>
            </div>

            <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm! w-[120px]!">name</label>
                <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="name" placeholder="Spanish" required>
            </div>

            {{-- <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm!">flag filename</label>
                <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!" name="flag" placeholder="en_US.png">
            </div> --}}

            <div class="text-right! mt-2.5!">
                <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-3! py-1.5! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! text-xs! md:py-1! md:px-2.5! md:text-sm!">create language</button>
            </div>
        </form>
    </div>

    <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">existing languages</div>
    <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">

        @forelse($languages as $lang)
            <div
                class="flex! justify-between! items-start! py-[15px]! border-b! border-[#ddd]!">

                {{-- LEFT INFO --}}
                <div class="min-w-[200px]!">
                    <div class="text-base! font-bold!">
                        {{ strtoupper($lang->code) }}: {{ $lang->name }}

                        @if ($lang->is_default)
                            <span
                                class="bg-[#28a745]! text-white! px-1.5! py-0.5! rounded! text-xs! ml-1.5!">
                                default
                            </span>
                        @endif
                    </div>

                    <div class="mt-[5px]! text-[13px]! text-[#666]!">
                        status: {{ $lang->is_active ? 'active' : 'inactive' }}
                    </div>
                </div>

                <div class="flex-1! max-w-[500px]!">

                    <form method="post" action="{{ route('admin.languages.update', $lang) }}"
                        class="flex! gap-2.5! items-center! mb-1.5!">
                        @csrf
                        @method('PUT')

                        <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[180px]!" name="name" value="{{ $lang->name }}">

                        {{-- <input class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white! max-w-[180px]!" name="flag" value="{{ $lang->flag }}"> --}}

                        <label class="flex! items-center! gap-[5px]!">
                            <input type="checkbox" name="is_active" {{ $lang->is_active ? 'checked' : '' }}>
                            active
                        </label>

                        <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-3! py-1.5! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! text-xs! md:py-1! md:px-2.5! md:text-sm!">save</button>
                    </form>

                    @if (!$lang->is_default)
                        <div class="flex! gap-2!">

                            <form method="post" action="{{ route('admin.languages.default', $lang) }}">
                                @csrf
                                <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-3! py-1.5! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! text-xs! md:py-1! md:px-2.5! md:text-sm!" type="submit">
                                    set default
                                </button>
                            </form>

                        </div>
                    @endif

                </div>

            </div>

        @empty
            <div class="p-5! text-center! text-[#777]!">
                no languages created yet
            </div>
        @endforelse

    </div>

@endsection
