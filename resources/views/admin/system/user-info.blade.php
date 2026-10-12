@extends('admin.layouts.app')

@section('title', 'User Info')

@section('content')

    <x-admin.page-header title="user info" />

    <form method="post" action="{{ route('admin.system.user_info.update') }}" data-admin-confirm="Save changes?">
        @csrf

        <x-admin.section-panel title="access data">
            <x-admin.form-field label="username">
                <div>{{ $admin->name }}</div>
            </x-admin.form-field>

            <x-admin.form-field label="current password">
                <x-ui.input name="current_password" type="password" class="w-full! box-border! border! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            <x-admin.form-field label="new password">
                <x-ui.input name="new_password" type="password" class="w-full! box-border! border! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>

            <x-admin.form-field label="repeat new password">
                <x-ui.input name="new_password_confirmation" type="password" class="w-full! box-border! border! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
        </x-admin.section-panel>


        <x-admin.section-panel title="misc">

            <x-admin.form-field label="email">
                <x-ui.input name="email" type="email" data-live-validate :value="old('email', $admin->email)" class="w-full! box-border! border! border-border! rounded! px-2! py-1.5! text-sm! bg-white!" />
            </x-admin.form-field>
        </x-admin.section-panel>

        <div class="mt-5!">
            <x-blatui.button type="submit">save</x-blatui.button>
        </div>

    </form>

@endsection
