@extends('admin.layouts.app')

@section('title', 'User Info')

@section('content')

    <h1 class="m-0! mb-2! text-lg!">user info</h1>

    <form method="post" action="{{ route('admin.system.user_info.update') }}" onsubmit="return confirm('Save changes?')">
        @csrf

        <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">access data</div>
        <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5! mb-5!">
            <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm!">username</label>
                <div>{{ $admin->name }}</div>
            </div>

            <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm!">current password</label>
                <input type="password" name="current_password" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!">
                {{-- @error('current_password')
                    <div class="fc-error">{{ $message }}</div>
                @enderror --}}
            </div>

            <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm!">new password</label>
                <input type="password" name="new_password" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!">
                {{-- @error('new_password')
                    <div class="fc-error">{{ $message }}</div>
                @enderror --}}
            </div>

            <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm!">repeat new password</label>
                <input type="password" name="new_password_confirmation" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!">
            </div>
        </div>


        <div class="inline-block! bg-[#e6e6e6]! border! border-[#9e9e9e]! border-b-0! px-2.5! py-[3px]! rounded-tl! rounded-tr! font-bold! text-sm! mt-2.5! mr-0! mb-0! ml-0!">misc</div>
        <div class="border! border-[#9e9e9e]! rounded! p-2.5! mb-2.5!">

            <div class="grid! grid-cols-1! gap-1.5! py-1.5! md:grid-cols-[160px_1fr]! md:gap-3! md:items-center!">
                <label class="font-bold! text-sm!">email</label>
                <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="w-full! box-border! border! border-[#bdbdbd]! rounded! px-2! py-1.5! text-sm! bg-white!">
                {{-- @error('email')
                    <div class="fc-error">{{ $message }}</div>
                @enderror --}}
            </div>
        </div>

        <div class="mt-5!">
            <button class="border! border-[#7d7d7d]! bg-[#e6e6e6]! px-3! py-1.5! rounded-[3px]! cursor-pointer! hover:bg-[#dcdcdc]! text-xs! md:py-1! md:px-2.5! md:text-sm!">
                save
            </button>
        </div>

    </form>

@endsection
