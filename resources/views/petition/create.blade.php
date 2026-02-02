@extends('layouts.legacy')

@section('title', 'Create petition - FreeCause')

@section('content')
    <section class="py-5">
        <div class="container">

            <h2 class="text-center mb-4">Start a free petition In Just 3 Easy Steps</h2>

            <div class="fc-steps mb-5">
                <div class="fc-step">
                    <span class="fc-step-icon fc-step-1 is-active"></span>
                    <div class="fc-step-text">Create your petition</div>
                </div>

                <div class="fc-step">
                    <span class="fc-step-icon fc-step-2"></span>
                    <div class="fc-step-text">Share with friends</div>
                </div>

                <div class="fc-step">
                    <span class="fc-step-icon fc-step-3"></span>
                    <div class="fc-step-text">Change the world!</div>
                </div>
            </div>


            @guest
                <div class="row g-4 mb-4">
                    <div class="col-lg-6">
                        @include('auth.partials._register_card', ['redirect' => url()->current()])
                    </div>
                    <div class="col-lg-6">
                        @include('auth.partials._login_card', ['redirect' => url()->current()])
                    </div>
                </div>
            @endguest

            <div class="card border-0 shadow-sm">
                <div class="card-body p-4">

                    <div class="mb-3">
                        <div class="fw-semibold">Petition Data</div>
                        <div style="height:2px;background:#d61f26;width:100%;margin-top:6px;"></div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)
                                    <li>{{ $e }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ lroute('petition.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Title (mandatory)</label>
                            <input class="form-control" name="title" value="{{ old('title') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Text (mandatory)</label>
                            <textarea class="form-control" name="description" rows="8"
                                required>{{ old('description') }}</textarea>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Goal (mandatory)</label>

                                <select class="form-select" name="goal_signatures" required>
                                    <option value="">(select one)</option>

                                    @php
                                        $goals = [
                                            50,
                                            100,
                                            1000,
                                            5000,
                                            10000,
                                            50000,
                                            100000,
                                            500000,
                                            1000000,
                                            10000000,
                                        ];

                                        $goalLabel = fn($n) => number_format($n, 0, '.', "'") . ' signatures';
                                    @endphp

                                    @foreach ($goals as $g)
                                        <option value="{{ $g }}" @selected((int) old('goal_signatures') === $g)>
                                            {{ $goalLabel($g) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Category (mandatory)</label>
                                <select class="form-select" name="category_id" required>
                                    <option value="">(select one)</option>
                                    @foreach($categories as $c)
                                        <option value="{{ $c->id }}" @selected(old('category_id') == $c->id)>{{ $c->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tags</label>
                            <input class="form-control" name="tags" value="{{ old('tags') }}">
                            <small class="text-muted">10 keywords max, separated by comma</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Image</label>
                            <input type="file" class="form-control" name="image">
                            <div class="mt-2">
                                <label class="form-label">Or supply an external link :</label>
                                <input class="form-control" name="image_url" value="{{ old('image_url') }}"
                                    placeholder="https://">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">URL Youtube video</label>
                            <input class="form-control" name="youtube" value="{{ old('youtube') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Petition target</label>
                            <input class="form-control" name="target" value="{{ old('target') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Petition community</label>
                            <input class="form-control" name="community" value="{{ old('community') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Petition community url</label>
                            <input class="form-control" name="community_url" value="{{ old('community_url') }}">
                        </div>

                        <div class="mb-4">
                            <label class="form-label">City</label>
                            <input class="form-control" name="city" value="{{ old('city') }}">
                        </div>

                        <div class="text-center">
                            @auth
                                <button class="btn btn-danger px-5">Submit</button>
                            @else
                                <button class="btn btn-danger px-5" disabled title="Please login first">Submit</button>
                                <div class="text-muted mt-2" style="font-size:14px;">Please login or register above to submit.
                                </div>
                            @endauth
                        </div>
                    </form>

                </div>
            </div>

        </div>
    </section>
@endsection

<style>
    .fc-steps {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 60px;
        flex-wrap: wrap;
        margin-bottom: 2.5rem;
    }

    .fc-step {
        display: flex;
        align-items: center;
        gap: 18px;
        min-width: 240px;
    }

    .fc-step-text {
        font-size: 20px;
        font-weight: 500;
        color: #111;
        white-space: nowrap;
    }

    .fc-step-icon {
        width: 64px;
        height: 64px;
        display: inline-block;
        background-image: url('{{ asset("legacy/images/startpetition-icons.png") }}');
        background-repeat: no-repeat;
        background-size: 200% 300%;
    }

    .fc-step-1 {
        background-position: 0% 0%;
    }

    .fc-step-2 {
        background-position: 0% 50%;
    }

    .fc-step-3 {
        background-position: 0% 100%;
    }

    .fc-step-icon.is-active.fc-step-1 {
        background-position: 100% 0%;
    }

    .fc-step-icon.is-active.fc-step-2 {
        background-position: 100% 50%;
    }

    .fc-step-icon.is-active.fc-step-3 {
        background-position: 100% 100%;
    }
</style>
