@extends('layouts.legacy')

@section('content')
<x-ui.container>
    <div class="flex flex-col gap-8 py-8">
        <h1 class="text-2xl">Components</h1>

        <section class="flex flex-wrap items-center gap-3" aria-label="Buttons">
            @foreach (['primary', 'secondary', 'danger', 'ghost'] as $variant)
                @foreach (['sm', 'md', 'lg'] as $size)
                    <x-ui.button :variant="$variant" :size="$size" type="button">{{ $variant }} {{ $size }}</x-ui.button>
                @endforeach
            @endforeach
            <x-ui.button type="button" :loading="true">Loading</x-ui.button>
            <x-ui.button href="#">Link</x-ui.button>
        </section>

        <section class="flex flex-wrap gap-2" aria-label="Badges">
            @foreach (['neutral', 'brand', 'success', 'warning', 'danger', 'info'] as $variant)
                <x-ui.badge :variant="$variant">{{ $variant }}</x-ui.badge>
            @endforeach
        </section>

        <section class="flex flex-col gap-3" aria-label="Alerts">
            @foreach (['success', 'error', 'warning', 'info'] as $variant)
                <x-ui.alert :variant="$variant">{{ $variant }} message</x-ui.alert>
            @endforeach
        </section>

        <section class="flex max-w-md flex-col gap-3" aria-label="Inputs">
            <x-ui.input name="preview_default" label="Default" />
            <x-ui.input name="preview_required" label="Required" :required="true" />
            <x-ui.input name="preview_error" label="Error" error="This field is required" />
        </section>

        <section aria-label="States" class="flex flex-col gap-4">
            <x-ui.empty title="Nothing here" text="Empty state text" />
            <x-ui.skeleton variant="title" />
            <x-ui.skeleton :lines="3" />
            <x-ui.spinner />
        </section>
    </div>
</x-ui.container>
@endsection
